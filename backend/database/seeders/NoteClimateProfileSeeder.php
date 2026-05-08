<?php

namespace Database\Seeders;

use App\Models\NoteClimateProfile;
use Illuminate\Database\Seeder;

/**
 * Seeder: NoteClimateProfileSeeder
 *
 * Pre-populates the note_climate_profiles table with ~60 of the most common
 * olfactive notes found on Fragrantica, each mapped to their ideal climate
 * parameters based on established perfumery conventions.
 *
 * ── How to read the data ─────────────────────────────────────────────────────
 *
 * Each note entry defines:
 *   name               : Canonical note name (Title Case) — must match what
 *                        PerfumAPI returns after normalization.
 *   note_family        : Broad olfactive family for grouping and engine fallback.
 *   min_temp_celsius   : Lower bound of ideal temperature. NULL = no lower limit.
 *   max_temp_celsius   : Upper bound. NULL = performs well at any temp above min.
 *   humidity_preference: 'low'|'medium'|'high'|'any'
 *   ideal_seasons      : Array of seasons this note is most beautiful in.
 *   climate_weight     : Engine influence (0.00–1.00). Higher = more decisive.
 *
 * ── Climate logic rationale ───────────────────────────────────────────────────
 *
 *  CITRUS (Bergamot, Lemon, etc.):
 *    Fresh, volatile top notes that evaporate quickly. Excel in warmth/heat
 *    where their brightness cuts through the air. Wasted in cold weather.
 *    → High temp, spring/summer, high weight (they define warm-weather character)
 *
 *  AQUATIC (Sea Salt, Marine, etc.):
 *    Synthetic notes designed to evoke cool ocean air and seaside freshness.
 *    Their "cooling" feel is most appreciated in peak summer heat.
 *    → High temp, summer only, high weight
 *
 *  FLORAL (Rose, Jasmine, etc.):
 *    Moderate and versatile. Rose is a four-season performer; specific florals
 *    like Lilac are strictly spring. Generally prefer mild temperatures.
 *    → Medium temp range, spring-focused, medium weight
 *
 *  WOODY (Sandalwood, Cedar, Vetiver):
 *    Warm, stable base notes that lend longevity. Most woody notes are year-round
 *    performers, skewing slightly cooler. Vetiver has a cooling facet that works
 *    well in tropical heat.
 *    → Wide temp range, weight varies by character
 *
 *  ORIENTAL/RESINOUS (Oud, Amber, Benzoin):
 *    Heavy, viscous, and incredibly potent. In heat and humidity, they become
 *    overwhelming and can project excessively. Best in cool/cold weather where
 *    their warmth is a comfort rather than an assault.
 *    → Low temp only, autumn/winter, high weight (they define character strongly)
 *
 *  GOURMAND (Vanilla, Caramel, Coffee):
 *    Sweet, food-like notes that create a cozy, enveloping warmth. Cloying in
 *    summer heat; divine in cold weather when comfort is sought.
 *    → Very low temp, winter only, high weight
 *
 *  SPICY (Cardamom, Cinnamon, Pepper):
 *    Complex category. Black Pepper can work in warmer weather. Heavier spices
 *    (Cinnamon, Clove) are strictly cold-weather notes.
 *    → Low-to-medium temp, autumn/winter typically
 *
 *  GREEN/HERBAL (Mint, Basil, Grass):
 *    Crisp, fresh, and evocative of dew-covered gardens. Best in mild spring
 *    temperatures where the cool air complements the herbal character.
 *    → Spring focus, medium weight
 *
 *  MUSK:
 *    The great versatile chameleon. Skin musks work year-round; clean musks
 *    excel in spring and summer. Low weight as they're rarely the dominant
 *    character but rather a background connector.
 *    → Wide range, lower weight
 */
class NoteClimateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notes = [

            // ── Citrus ────────────────────────────────────────────────────
            [
                'name'                => 'Bergamot',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Lemon',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Grapefruit',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Neroli',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 16,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Mandarin Orange',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.65,
            ],

            // ── Aquatic ───────────────────────────────────────────────────
            [
                'name'                => 'Sea Salt',
                'note_family'         => 'Aquatic',
                'min_temp_celsius'    => 22,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.85,
            ],
            [
                'name'                => 'Marine',
                'note_family'         => 'Aquatic',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.85,
            ],
            [
                'name'                => 'Aquatic Notes',
                'note_family'         => 'Aquatic',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.80,
            ],

            // ── Floral ────────────────────────────────────────────────────
            [
                'name'                => 'Rose',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Jasmine',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Iris',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Peony',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 25,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Tuberose',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['summer', 'spring'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Ylang-Ylang',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => 32,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Violet',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],

            // ── Woody ─────────────────────────────────────────────────────
            [
                'name'                => 'Sandalwood',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 25,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter', 'spring'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Cedar',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn', 'winter'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Vetiver',
                'note_family'         => 'Earthy',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 35,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Patchouli',
                'note_family'         => 'Earthy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Oakmoss',
                'note_family'         => 'Earthy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Guaiac Wood',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter', 'spring'],
                'climate_weight'      => 0.55,
            ],

            // ── Oriental / Resinous ───────────────────────────────────────
            [
                'name'                => 'Oud',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.90,
            ],
            [
                'name'                => 'Amber',
                'note_family'         => 'Amber',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 16,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Ambergris',
                'note_family'         => 'Amber',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter', 'spring'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Benzoin',
                'note_family'         => 'Balsamic',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Frankincense',
                'note_family'         => 'Balsamic',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 15,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Labdanum',
                'note_family'         => 'Amber',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 15,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Myrrh',
                'note_family'         => 'Balsamic',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.70,
            ],

            // ── Gourmand ──────────────────────────────────────────────────
            [
                'name'                => 'Vanilla',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.85,
            ],
            [
                'name'                => 'Caramel',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 10,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Chocolate',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 10,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Coffee',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 14,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Tonka Bean',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 16,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Praline',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['winter'],
                'climate_weight'      => 0.70,
            ],

            // ── Spicy ─────────────────────────────────────────────────────
            [
                'name'                => 'Black Pepper',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn', 'winter'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Pink Pepper',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Cardamom',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Cinnamon',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 14,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Clove',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Saffron',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Ginger',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.60,
            ],

            // ── Green / Herbal ────────────────────────────────────────────
            [
                'name'                => 'Mint',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Basil',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Green Tea',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Grass',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 25,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Violet Leaf',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.50,
            ],
            [
                'name'                => 'Lavender',
                'note_family'         => 'Aromatic',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Rosemary',
                'note_family'         => 'Aromatic',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 25,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],

            // ── Musk ──────────────────────────────────────────────────────
            [
                'name'                => 'Musk',
                'note_family'         => 'Musk',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn', 'winter'],
                'climate_weight'      => 0.35,
            ],
            [
                'name'                => 'White Musk',
                'note_family'         => 'Musk',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.40,
            ],

            // ── Fruity ────────────────────────────────────────────────────
            [
                'name'                => 'Peach',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 16,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Raspberry',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Apple',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 25,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Blackcurrant',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 24,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.55,
            ],

            // ── Powdery ───────────────────────────────────────────────────
            [
                'name'                => 'Orris Root',
                'note_family'         => 'Powdery',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['spring', 'autumn', 'winter'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Heliotrope',
                'note_family'         => 'Powdery',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.60,
            ],

            // ── Leather ───────────────────────────────────────────────────
            [
                'name'                => 'Leather',
                'note_family'         => 'Leather',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 16,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Suede',
                'note_family'         => 'Leather',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.70,
            ],

            // ── Iso E Super (a widely-used synthetic) ─────────────────────
            [
                'name'                => 'Iso E Super',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn', 'winter'],
                'climate_weight'      => 0.40,
            ],
        ];

        foreach ($notes as $note) {
            NoteClimateProfile::updateOrCreate(
                ['name' => $note['name']], // Match on canonical name
                $note
            );
        }

        $this->command->info('  Seeded ' . count($notes) . ' note climate profiles.');
    }
}
