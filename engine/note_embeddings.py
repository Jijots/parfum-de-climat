"""
note_embeddings.py — Parfum de Climat Semantic Note Matching

Matches leftover fragrance note names onto the NoteClimateProfile taxonomy
using transformer sentence embeddings. This is the genuine machine-learning
pass; note_mapper.py handles everything string matching can reach first.

── What makes this ML, when TF-IDF is not ───────────────────────────────────
The model (all-MiniLM-L6-v2) is a neural network whose weights were LEARNED by
gradient descent over roughly a billion sentence pairs. Nobody wrote a rule
saying "agarwood means oud" — the model inferred that relationship from how the
words are used in text. Using those learned weights on a task the model was
never specifically trained for is transfer learning.

Contrast with similarity.py: TF-IDF has no learned weights at all. It is a
counting formula from 1972. Both are useful; only one is machine learning.

── Why embeddings catch what string matching cannot ─────────────────────────
String matching compares SHAPE. Embeddings compare MEANING.

    "Agarwood" vs "Oud"          share 0 letters  -> string matching fails
                                 nearly identical meaning -> embeddings succeed

Every name becomes a vector of 384 numbers. Names used in similar contexts land
near each other, so cosine similarity between vectors approximates similarity
of meaning.

── An honest limitation ─────────────────────────────────────────────────────
Embeddings always return a nearest neighbour, even when nothing genuinely
matches. Most remaining unmapped notes (Geranium, Freesia, Pear) have NO
corresponding climate profile, so the correct answer is "no match" — and the
model has no way to say that. The threshold is what forces it to abstain, and
it is set deliberately high. Treat the output as suggestions for review, not
as ground truth.

Requires:
  pip install sentence-transformers

Called by: php artisan notes:map --method=embed
Usage:     python note_embeddings.py <input.json> <output.json>
"""

from __future__ import annotations

import json
import sys

# Cosine similarity floor. Below this the match is discarded as "no profile
# fits this note", which for this dataset is usually the correct answer.
#
# Set high on purpose. The model will happily report 0.5 similarity between
# two entirely unrelated notes simply because both are perfume words.
DEFAULT_THRESHOLD = 0.72

# Small, fast, CPU-friendly, ~80 MB. Downloads once to ~/.cache and is then
# available offline. Bigger models exist but are not worth the size here:
# note names are one to three words, so there is little context to exploit.
MODEL_NAME = "sentence-transformers/all-MiniLM-L6-v2"

# Generic category words that must never be auto-mapped onto a specific profile.
#
# These are the model's characteristic failure mode, and it is worth being
# precise about why. Run against the real catalog it proposed:
#
#   Citruses -> Lemon       0.92
#   Spices   -> Cinnamon    0.90
#   Orange   -> Mandarin Orange 0.91
#
# Each is confidently wrong. "Spices" is not cinnamon; it is a category that
# happens to contain cinnamon. The embedding sits close because the words are
# genuinely related — relatedness is what the model measures, and it has no
# notion of "this is a broader term than that".
#
# Notably the string matcher made the identical Orange -> Mandarin Orange
# mistake before its containment rule was removed. The failure is not about
# spelling versus meaning; it is that a generic term has no correct specific
# answer. The honest result is no match, which tells us the taxonomy is missing
# an entry rather than inviting us to corrupt an existing one.
GENERIC_TERMS: frozenset[str] = frozenset({
    "citrus", "citruses", "citrus notes", "spices", "spice", "spicy notes",
    "green notes", "woody notes", "woodsy notes", "floral notes", "flowers",
    "fruity notes", "fruits", "berries", "red berries", "white flowers",
    "aromatic notes", "powdery notes", "sweet notes", "balsamic notes",
    "earthy notes", "animal notes", "mineral notes", "orange", "pepper",
    "tea", "wood", "woods", "musky notes", "amber notes", "resins",
    "herbs", "aldehydes", "moss", "leaves", "roots", "petals", "seeds",
})

# Specific notes with no correct profile, where the model still returns a
# confident wrong answer. Unlike GENERIC_TERMS these are not category words —
# they are real materials the taxonomy simply does not cover yet.
#
#   Grapes      -> Grapefruit  0.94, margin 0.16
#   Grape Leaves-> Grapefruit  0.94, margin 0.16
#
# Grapes are not grapefruit. The model is pulled in by the shared prefix and by
# both being fruit, and it reports the match decisively — the runner-up is far
# behind. That is worth noting: MARGIN MEASURES DECISIVENESS, NOT CORRECTNESS.
# A confident, unambiguous, wrong answer looks exactly like a confident, correct
# one from the inside. Only a human who knows the domain can separate them,
# which is why --apply is opt-in and the dry run prints the full proposal.
NEVER_MAP: frozenset[str] = frozenset({
    "grapes", "grape", "grape leaves", "grape leaf",
})


def contextualise(name: str) -> str:
    """
    Wrap a bare note name in a sentence before embedding it.

    Embedding models are trained on prose, so an isolated token like "Oud"
    carries little signal. Giving it a natural frame puts the vector in the
    right region of the space and measurably improves the pairing.

        "Oud"  ->  "Oud, a fragrance note used in perfume"
    """
    return f"{name}, a fragrance note used in perfume"


def run(profiles: list[str], unmapped: list[dict], threshold: float) -> dict:
    try:
        import numpy as np
        from sentence_transformers import SentenceTransformer
    except ImportError as exc:
        raise RuntimeError(
            "sentence-transformers is required for the embedding pass.\n"
            "Run: pip install sentence-transformers\n"
            f"({exc})"
        )

    model = SentenceTransformer(MODEL_NAME)

    profile_texts = [contextualise(p) for p in profiles]
    note_texts    = [contextualise(entry["name"]) for entry in unmapped]

    # normalize_embeddings=True makes every vector unit length, so the cosine
    # similarity between any two of them is just their dot product.
    profile_vecs = model.encode(
        profile_texts, normalize_embeddings=True, show_progress_bar=False
    )
    note_vecs = model.encode(
        note_texts, normalize_embeddings=True, batch_size=64, show_progress_bar=False
    )

    # (n_notes x 384) @ (384 x n_profiles) -> (n_notes x n_profiles)
    # One matrix multiply scores every note against every profile at once.
    scores = note_vecs @ profile_vecs.T

    matches: list[dict] = []
    unmatched: list[dict] = []

    for i, entry in enumerate(unmapped):
        row  = scores[i]
        best = int(np.argmax(row))
        score = float(row[best])

        # A generic category has no correct specific answer, and the model
        # cannot abstain on its own — it always returns a nearest neighbour.
        name_key = entry["name"].strip().lower()
        if name_key in GENERIC_TERMS or name_key in NEVER_MAP:
            unmatched.append(entry)
            continue

        if score >= threshold:
            matches.append({
                "raw":        entry["name"],
                "profile":    profiles[best],
                "confidence": round(score, 3),
                "method":     "embedding",
                "count":      entry.get("count", 0),
                # The runner-up is reported so a reviewer can see how decisive
                # the win was. A narrow gap means the model was nearly guessing.
                "runner_up":  profiles[int(np.argsort(row)[-2])] if len(profiles) > 1 else None,
                "margin":     round(score - float(np.sort(row)[-2]), 3) if len(profiles) > 1 else 0.0,
            })
        else:
            unmatched.append(entry)

    matches.sort(key=lambda m: (-m["count"], m["raw"]))
    unmatched.sort(key=lambda u: -u.get("count", 0))

    return {"matches": matches, "unmatched": unmatched}


def main() -> int:
    if len(sys.argv) < 3:
        sys.stderr.write("Usage: python note_embeddings.py <input.json> <output.json>\n")
        return 1

    try:
        with open(sys.argv[1], encoding="utf-8") as handle:
            payload = json.load(handle)
    except Exception as exc:
        sys.stderr.write(f"Could not read input: {exc}\n")
        return 1

    threshold = float(payload.get("threshold", DEFAULT_THRESHOLD))

    try:
        result = run(payload["profiles"], payload["unmapped"], threshold)
    except RuntimeError as exc:
        sys.stderr.write(f"{exc}\n")
        return 1
    except Exception as exc:
        sys.stderr.write(f"Embedding pass failed: {exc}\n")
        return 2

    try:
        with open(sys.argv[2], "w", encoding="utf-8") as handle:
            json.dump(result, handle)
    except Exception as exc:
        sys.stderr.write(f"Could not write output: {exc}\n")
        return 2

    sys.stdout.write(f"OK {len(result['matches'])} {len(result['unmatched'])}\n")
    return 0


if __name__ == "__main__":
    sys.exit(main())
