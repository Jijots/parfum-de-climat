"""
note_mapper.py — Parfum de Climat Note Normalisation

Matches unmapped fragrance note names onto the existing NoteClimateProfile
taxonomy using string normalisation, a curated synonym table, and fuzzy
matching. Deliberately contains NO machine learning — see note_embeddings.py
for the semantic pass that handles what string matching cannot.

Why string matching first: roughly half of the unmapped names are ordinary
spelling and formatting variants ("Black Currant" vs "Blackcurrant", "Cloves"
vs "Clove", "Vanille" vs "Vanilla"). A normaliser resolves those exactly,
deterministically, and in milliseconds. Reaching for an embedding model to do
this would be slower, less predictable, and no more accurate.

Called by: php artisan notes:map
Usage:     python note_mapper.py <input.json> <output.json>

Input JSON:
  {"profiles": ["Bergamot", ...],
   "unmapped": [{"name": "Black Currant", "count": 1586}, ...]}

Output JSON:
  {"matches": [{"raw": ..., "profile": ..., "confidence": 0.95, "method": ...}, ...],
   "unmatched": [{"name": ..., "count": 123}, ...]}
"""

from __future__ import annotations

import json
import re
import sys
import unicodedata
from difflib import SequenceMatcher

# ─────────────────────────────────────────────────────────────────────────────
# Domain knowledge
# ─────────────────────────────────────────────────────────────────────────────

# Perfumery synonyms. These are cases where two names refer to the same
# material but share little or no spelling, so no amount of fuzzy matching
# finds them. Curated by hand.
SYNONYMS: dict[str, str] = {
    "agarwood":        "oud",
    "aoud":            "oud",
    "oudh":            "oud",
    "olibanum":        "frankincense",
    "cassis":          "blackcurrant",
    "vanille":         "vanilla",
    "orris":           "orris root",
    "iris root":       "orris root",
    "orange blossom":  "neroli",       # neroli is distilled from orange blossom
    "ambroxan":        "ambergris",    # synthetic ambergris substitute
    "ambrox":          "ambergris",
    "ambroxide":       "ambergris",
    "calone":          "marine",       # the classic aquatic/melon molecule
    "moss":            "oakmoss",
    "tree moss":       "oakmoss",
    "labdanum resin":  "labdanum",
    "tonka":           "tonka bean",
    "ylang ylang":     "ylang-ylang",
    "pink peppercorn": "pink pepper",
    "peppercorn":      "black pepper",
    "cacao":           "chocolate",
    "cocoa":           "chocolate",
    "vetivert":        "vetiver",
    "guaiacwood":      "guaiac wood",
    "guajac wood":     "guaiac wood",
    "sea water":       "marine",
    "sea":             "marine",       # "Sea Notes" reduces to "sea"
    "ocean":           "marine",
    "aquatic":         "aquatic note",
    "salt":            "sea salt",
    "mandarin":        "mandarin orange",
    "incense":         "frankincense",  # frankincense is the dominant incense resin
    "encens":          "frankincense",
    "benzoin siam":    "benzoin",
    "styrax":          "benzoin",
}

# Geographic and quality qualifiers that narrow a material without changing it.
# "Bulgarian Rose" is a rose; "Virginia Cedar" is a cedar. Stripping these lets
# the remainder match the base profile.
#
# Colour words are deliberately absent: "Black Pepper", "Pink Pepper" and
# "White Musk" are distinct profiles, so stripping "black" or "white" would
# collapse genuinely different entries onto each other.
QUALIFIERS: set[str] = {
    "bulgarian", "turkish", "damask", "damascena", "moroccan", "egyptian",
    "indian", "french", "italian", "sicilian", "amalfi", "calabrian",
    "virginia", "texas", "atlas", "himalayan", "australian", "brazilian",
    "madagascar", "tahitian", "bourbon", "javanese", "chinese", "japanese",
    "russian", "spanish", "haitian", "somali", "yemeni", "cambodian",
    "sambac", "grandiflorum", "absolute", "essence", "extract", "oil",
    "wild", "fresh", "dried", "smoked", "toasted", "candied", "sweet",
    "bitter", "sour", "clary", "true", "common", "garden",
}

# Trailing words that add no material meaning: "Woody Notes" becomes "woody".
NOISE_SUFFIXES: tuple[str, ...] = ("notes", "note", "accord", "accords")

# Fuzzy matches below this similarity ratio are discarded as unreliable.
#
# Tuned against the real catalog. At 0.88 this admitted "Clover" -> "Clove"
# (ratio 0.909), which is a different plant entirely. 0.93 rejects that while
# still accepting the genuine variants it needs to catch:
#
#   Black Currant -> Blackcurrant   0.96
#   Graperfuit    -> Grapefruit     0.94   (typo in the source data)
#   Oak Moss      -> Oakmoss        0.93
FUZZY_THRESHOLD = 0.93


# ─────────────────────────────────────────────────────────────────────────────
# Normalisation
# ─────────────────────────────────────────────────────────────────────────────

def strip_accents(text: str) -> str:
    """Acacia with an accent becomes plain Acacia — the catalog mixes both."""
    return "".join(
        c for c in unicodedata.normalize("NFKD", text)
        if not unicodedata.combining(c)
    )


def singularise(word: str) -> str:
    """
    Crude English singulariser, sufficient for note names.

    Guards against destroying words that merely END in s without being plural.
    Perfumery is full of them, and getting this wrong silently breaks lookups:

        cassis -> cassi   would miss the cassis/blackcurrant synonym  (564 rows)
        orris  -> orri    would miss Orris Root
        iris   -> iri     would miss the Iris profile entirely
        moss   -> mos     would miss the moss/oakmoss synonym
    """
    if len(word) < 4 or word.endswith(("ss", "us", "is")):
        return word
    if word.endswith("ies"):
        return word[:-3] + "y"
    if word.endswith("es") and word[-3:-2] in ("h", "s", "x", "z"):
        return word[:-2]
    if word.endswith("s"):
        return word[:-1]
    return word


def normalise(name: str, *, drop_qualifiers: bool = False) -> str:
    """
    Reduce a note name to a comparable canonical form.

      Lily-of-the-valley  ->  lily of the valley
      Woody Notes         ->  woody
      Citruses            ->  citrus
      Virginia Cedar      ->  cedar   (drop_qualifiers=True)
    """
    text = strip_accents(name).lower().strip()

    # Drop parenthetical asides: "Agarwood (oud)" becomes "agarwood"
    text = re.sub(r"\([^)]*\)", " ", text)

    # Punctuation and separators become spaces
    text = re.sub(r"[-_/,.]", " ", text)
    text = re.sub(r"[^a-z0-9 ]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()

    words = text.split()

    # Strip trailing noise words such as "notes" or "accord"
    while words and words[-1] in NOISE_SUFFIXES:
        words.pop()

    if drop_qualifiers:
        stripped = [w for w in words if w not in QUALIFIERS]
        # Never strip everything away — "Absolute" alone must stay "absolute"
        if stripped:
            words = stripped

    words = [singularise(w) for w in words]

    return " ".join(words)


def parenthetical(name: str) -> str | None:
    """Agarwood (oud) yields "oud" — the aside often holds the commoner name."""
    match = re.search(r"\(([^)]+)\)", name)
    return normalise(match.group(1)) if match else None


# ─────────────────────────────────────────────────────────────────────────────
# Matching
# ─────────────────────────────────────────────────────────────────────────────

def build_profile_index(profiles: list[str]) -> dict[str, str]:
    """Map every normalised form of a profile name back to its canonical name."""
    index: dict[str, str] = {}
    for profile in profiles:
        index[normalise(profile)] = profile
        index[normalise(profile, drop_qualifiers=True)] = profile
    return index


def match_one(
    raw: str,
    index: dict[str, str],
    profiles: list[str],
) -> tuple[str, float, str] | None:
    """
    Resolve a single raw note name to a profile.

    Strategies are tried in descending order of trust; the first hit wins.
    Returns (profile_name, confidence, method) or None.
    """
    base = normalise(raw)

    # 1. Exact match after normalisation — the safest possible result.
    if base in index:
        return index[base], 1.0, "exact"

    # 2. Curated synonym, then re-check the index.
    if base in SYNONYMS:
        target = normalise(SYNONYMS[base])
        if target in index:
            return index[target], 0.97, "synonym"

    # 3. The parenthetical aside: "Agarwood (oud)" resolves via "oud".
    aside = parenthetical(raw)
    if aside:
        if aside in index:
            return index[aside], 0.95, "parenthetical"
        if aside in SYNONYMS and normalise(SYNONYMS[aside]) in index:
            return index[normalise(SYNONYMS[aside])], 0.95, "parenthetical"

    # 4. Drop geographic/quality qualifiers: "Bulgarian Rose" becomes "rose".
    plain = normalise(raw, drop_qualifiers=True)
    if plain != base and plain in index:
        return index[plain], 0.92, "qualifier"
    if plain in SYNONYMS and normalise(SYNONYMS[plain]) in index:
        return index[normalise(SYNONYMS[plain])], 0.92, "qualifier"

    # NOTE: an earlier version matched on whole-word containment — if the raw
    # name was a strict subset of exactly one profile's words, it was accepted.
    # Tested against the real catalog that rule was wrong about as often as it
    # was right, because containment does not imply equivalence:
    #
    #   Orange      < Mandarin Orange   a mandarin orange is a KIND of orange,
    #                                   so this maps generic onto specific
    #   Green Notes < Green Tea         a generic green accord is not tea
    #   Black       < Black Pepper      "Black" alone is junk source data
    #   Roots       < Orris Root        meaningless
    #
    # The handful of legitimate cases ("Orris" for Orris Root, "Mandarin" for
    # Mandarin Orange) are real domain knowledge rather than anything derivable
    # from string shape, so they live in SYNONYMS where they can be reviewed.

    # 5. Fuzzy match — catches residual typos and spacing variants.
    best, best_ratio = None, 0.0
    for norm_form, canonical in index.items():
        ratio = SequenceMatcher(None, base, norm_form).ratio()
        if ratio > best_ratio:
            best, best_ratio = canonical, ratio

    if best and best_ratio >= FUZZY_THRESHOLD:
        return best, round(best_ratio, 3), "fuzzy"

    return None


def run(profiles: list[str], unmapped: list[dict]) -> dict:
    index = build_profile_index(profiles)
    matches: list[dict] = []
    unmatched: list[dict] = []

    for entry in unmapped:
        raw = entry["name"]
        result = match_one(raw, index, profiles)

        if result:
            profile, confidence, method = result
            matches.append({
                "raw":        raw,
                "profile":    profile,
                "confidence": confidence,
                "method":     method,
                "count":      entry.get("count", 0),
            })
        else:
            unmatched.append(entry)

    matches.sort(key=lambda m: (-m["count"], m["raw"]))
    unmatched.sort(key=lambda u: -u.get("count", 0))

    return {"matches": matches, "unmatched": unmatched}


def main() -> int:
    if len(sys.argv) < 3:
        sys.stderr.write("Usage: python note_mapper.py <input.json> <output.json>\n")
        return 1

    try:
        with open(sys.argv[1], encoding="utf-8") as handle:
            payload = json.load(handle)
    except Exception as exc:
        sys.stderr.write(f"Could not read input: {exc}\n")
        return 1

    try:
        result = run(payload["profiles"], payload["unmapped"])
        with open(sys.argv[2], "w", encoding="utf-8") as handle:
            json.dump(result, handle)
    except Exception as exc:
        sys.stderr.write(f"Mapping failed: {exc}\n")
        return 2

    sys.stdout.write(f"OK {len(result['matches'])} {len(result['unmatched'])}\n")
    return 0


if __name__ == "__main__":
    sys.exit(main())
