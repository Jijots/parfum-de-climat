"""
similarity.py — Parfum de Climat Content-Based Similarity Index

Reads a JSON file of fragrances + raw note names, computes TF-IDF vectors,
and finds the top-10 most similar fragrances per fragrance using cosine
similarity. Writes the result pairs as JSON to stdout.

Called by: php artisan fragrances:build-similarity-index
Not called at request time — this runs once to populate the
fragrance_similarities table in the database.

Requires:
  pip install -r engine/requirements-similarity.txt

Usage (Artisan command handles this automatically):
  python similarity.py <input_corpus.json> <output_pairs.json>

Input JSON (written by the Artisan command):
  [{"id": 1, "notes": ["Bergamot", "Rose", "Musk"]}, ...]

Output JSON (written to the output file path):
  {"pairs": [{"fragrance_id": 1, "similar_id": 2, "score": 0.87, "rank": 1}, ...]}

Both sides use files rather than stdin/stdout pipes — see main() for why.
"""

from __future__ import annotations

import json
import sys

TOP_N       = 10    # similar fragrances to keep per fragrance
CHUNK_SIZE  = 1000  # fragrances per similarity batch (controls peak RAM)


def note_token(name: str) -> str:
    """
    Normalise a note name to a single token.
    "Lily of the Valley" → "lily_of_the_valley"

    This keeps multi-word note names together so TF-IDF treats them
    as one feature, not three separate words.
    """
    return name.lower().strip().replace(" ", "_").replace("-", "_")


def build_index(fragrances: list[dict]) -> list[dict]:
    """
    Compute top-N similarity pairs for all fragrances.

    Steps:
      1. Build a TF-IDF matrix: each row = one fragrance, each column = one note.
         Values are TF-IDF weights (high for rare distinctive notes, low for common ones).
      2. Compute cosine similarity in chunks to avoid a 24k×24k dense matrix in RAM.
      3. For each fragrance, record the top-10 most similar others.

    Memory profile: ~100 MB peak for CHUNK_SIZE=1000, n=24000 fragrances.
    """
    try:
        import numpy as np
        from sklearn.feature_extraction.text import TfidfVectorizer
        from sklearn.metrics.pairwise import cosine_similarity
    except ImportError:
        raise RuntimeError(
            "scikit-learn and numpy are required.\n"
            "Run: pip install -r engine/requirements-similarity.txt"
        )

    ids = [f["id"] for f in fragrances]

    # Each fragrance becomes a space-separated string of note tokens.
    # A note appearing in top + base layers will appear twice — TF rewards frequency.
    # Fragrances with no notes get a placeholder token that scores 0 against everything.
    documents = []
    for f in fragrances:
        tokens = [note_token(n) for n in f.get("notes", []) if n.strip()]
        documents.append(" ".join(tokens) if tokens else "__no_notes__")

    # TF-IDF vectorization
    #   token_pattern: matches underscore-joined note tokens like "lily_of_the_valley"
    #   min_df=3:      discard notes that appear in fewer than 3 fragrances (noise/typos)
    #   sublinear_tf:  use log(1 + raw_count) so notes appearing 10x aren't 10× more
    #                  important than notes appearing once
    vectorizer = TfidfVectorizer(
        token_pattern=r"\b[a-z_][a-z0-9_]+\b",
        min_df=3,
        sublinear_tf=True,
    )
    tfidf_matrix = vectorizer.fit_transform(documents)  # sparse CSR, shape: (n, vocab_size)

    n     = len(fragrances)
    pairs: list[dict] = []
    top_n = min(TOP_N, n - 1)  # can't have more similar than n − 1

    if top_n <= 0:
        return pairs

    # Chunk-wise cosine similarity.
    # For each chunk of CHUNK_SIZE fragrances, compute their similarity against
    # all n fragrances. The result is a (CHUNK_SIZE × n) dense matrix.
    # We immediately extract the top-N from each row, then discard the dense chunk.
    for start in range(0, n, CHUNK_SIZE):
        end          = min(start + CHUNK_SIZE, n)
        chunk        = tfidf_matrix[start:end]
        sim_block    = cosine_similarity(chunk, tfidf_matrix).astype("float32")

        for local_i, row in enumerate(sim_block):
            global_i = start + local_i

            # Zero out self-similarity (always 1.0) so a fragrance never
            # appears as "similar to itself".
            row[global_i] = 0.0

            # argpartition is O(n) vs O(n log n) for argsort —
            # it finds the top-N indices without fully sorting the array.
            top_idxs = np.argpartition(row, -top_n)[-top_n:]
            top_idxs = top_idxs[np.argsort(row[top_idxs])[::-1]]  # sort just the top-N

            for rank, j in enumerate(top_idxs, start=1):
                score = float(row[j])
                if score < 0.01:
                    break  # remaining similarities are negligible
                pairs.append({
                    "fragrance_id": ids[global_i],
                    "similar_id":   ids[j],
                    "score":        round(score, 4),
                    "rank":         rank,
                })

    return pairs


def _fail(message: str, code: int) -> int:
    """Report an error on stderr and exit. Kept short — stderr is pipe-buffered."""
    sys.stderr.write(message[:2000] + "\n")
    sys.stderr.flush()
    return code


def main() -> int:
    """
    Results are written to an OUTPUT FILE, not stdout.

    Piping a multi-megabyte JSON payload through stdout deadlocks: the OS pipe
    buffer (~4-64 KB) fills, Python blocks mid-write, and the parent process is
    waiting on a stream_select() that never fires — stream_select() does not
    support pipes on Windows. Writing to a file sidesteps pipes entirely and
    behaves identically on every platform.

    stdout/stderr carry only short status text, which always fits in the buffer.
    """
    if len(sys.argv) < 3:
        return _fail("Usage: python similarity.py <corpus.json> <output.json>", 1)

    corpus_path, output_path = sys.argv[1], sys.argv[2]

    try:
        with open(corpus_path, encoding="utf-8") as fh:
            fragrances = json.load(fh)
    except Exception as e:
        return _fail(f"Could not read corpus file: {e}", 1)

    if not isinstance(fragrances, list) or not fragrances:
        return _fail("Corpus must be a non-empty JSON array.", 1)

    try:
        pairs = build_index(fragrances)
    except RuntimeError as e:
        return _fail(str(e), 1)
    except Exception as e:
        return _fail(f"Similarity computation failed: {e}", 2)

    try:
        with open(output_path, "w", encoding="utf-8") as fh:
            json.dump({"pairs": pairs, "count": len(pairs)}, fh)
    except Exception as e:
        return _fail(f"Could not write output file: {e}", 2)

    # Short status line only — safe to send through the pipe.
    sys.stdout.write(f"OK {len(pairs)}\n")
    sys.stdout.flush()
    return 0


if __name__ == "__main__":
    sys.exit(main())
