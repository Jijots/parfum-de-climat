<?php

namespace Database\Seeders;

use App\Models\NoteClimateProfile;
use Illuminate\Database\Seeder;

/**
 * Seeder: NoteClimateProfileGapSeeder
 *
 * Second taxonomy expansion. Closes the largest remaining note gaps after
 * NoteClimateProfileExpansionSeeder.
 *
 * ── Still deliberately excluded ──────────────────────────────────────────────
 * The biggest remaining unmapped names are category labels, not materials:
 * Woody Notes (1,318), Green Notes (1,065), Citruses (967), Woodsy Notes (964),
 * Floral Notes, Spices, White Flowers, Fruity Notes, Red Berries, Tea, Pepper.
 *
 * Giving those a climate window would be inventing data. "Spices" spans
 * cinnamon (warm, winter) and coriander (fresh, spring); no single temperature
 * range is honest for both. They stay unmapped on purpose, and the engine
 * simply ignores them — which is correct, since they carry no real signal about
 * when a fragrance is wearable.
 *
 * ── Provenance ───────────────────────────────────────────────────────────────
 * Values follow perfumery convention, checked against published note
 * references. A few worth calling out:
 *
 *   Aldehydes  perform best above ~15C; below that the sparkle flattens
 *   Cashmeran  a cold-weather base fixative, cosy and dry rather than fresh
 *   Ambrette   shifts with temperature — cool brings out its creamy musk,
 *              warmth its fruity facets — hence a wide window, modest weight
 *
 * These are editorial judgements, like the rest of the taxonomy. Tune them
 * against how the recommendations actually feel.
 *
 * Usage:
 *   php artisan db:seed --class=NoteClimateProfileGapSeeder
 *   php artisan notes:map --apply
 */
class NoteClimateProfileGapSeeder extends Seeder
{
    public function run(): void
    {
        $notes = [

            // ── Citrus ────────────────────────────────────────────────────
            // Sweet orange: rounder and less sharp than lemon, so it holds up
            // a little lower down the temperature range.
            [
                'name'                => 'Orange',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Tangerine',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'winter'],
                'climate_weight'      => 0.65,
            ],

            // ── Abstract / synthetic ──────────────────────────────────────
            // Aldehydes: the soapy, sparkling lift of the classic florals.
            // The sparkle needs warmth to read; it goes flat in real cold.
            [
                'name'                => 'Aldehydes',
                'note_family'         => 'Aldehydic',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.50,
            ],
            // Cashmeran / cashmere wood: dry, faintly spicy, musky-woody. A
            // base fixative that reads as cosy, so it belongs to cold weather.
            [
                'name'                => 'Cashmere Wood',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => -10,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.55,
            ],
            // Ambrette (musk mallow): a botanical musk. Cool weather brings out
            // the creamy powdery base, warmth the fruity facets — genuinely
            // seasonless, so the window is wide and the weight low.
            [
                'name'                => 'Ambrette',
                'note_family'         => 'Musk',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn', 'winter'],
                'climate_weight'      => 0.40,
            ],

            // ── Florals ───────────────────────────────────────────────────
            [
                'name'                => 'Lily',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            // Honeysuckle: sweet and honeyed, close to jasmine. A high-summer
            // hedgerow smell.
            [
                'name'                => 'Honeysuckle',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 16,
                'max_temp_celsius'    => 32,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            // Cyclamen: watery and translucent rather than sweet.
            [
                'name'                => 'Cyclamen',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.55,
            ],
            // Narcissus: green, hay-like, faintly animalic — a spring floral
            // with more weight than the white flowers around it.
            [
                'name'                => 'Narcissus',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.55,
            ],
            // Water lily: aquatic floral, reads as cooling.
            [
                'name'                => 'Water Lily',
                'note_family'         => 'Aquatic',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.65,
            ],

            // ── Fruit ─────────────────────────────────────────────────────
            [
                'name'                => 'Litchi',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Melon',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.65,
            ],

            // ── Aromatic / boozy ──────────────────────────────────────────
            // Juniper: fresh, resinous, gin-like, faintly spicy. Crisp rather
            // than warm, so it skews to the cooler half of the year.
            [
                'name'                => 'Juniper',
                'note_family'         => 'Aromatic',
                'min_temp_celsius'    => 0,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.55,
            ],
            // Rum: boozy, sweet, warming — a cold-weather gourmand.
            [
                'name'                => 'Rum',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => -10,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.65,
            ],
        ];

        foreach ($notes as $note) {
            NoteClimateProfile::updateOrCreate(
                ['name' => $note['name']],
                $note
            );
        }

        $this->command->info('  Seeded ' . count($notes) . ' gap-closing note climate profiles.');
    }
}
