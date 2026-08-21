<?php

namespace Database\Seeders;

use App\Models\NoteClimateProfile;
use Illuminate\Database\Seeder;

/**
 * Seeder: NoteClimateProfileExpansionSeeder
 *
 * Adds the 24 most common notes in the catalog that had no climate profile.
 *
 * ── Why these notes ──────────────────────────────────────────────────────────
 * After `notes:map` ran both its passes, ~1,400 distinct note names were still
 * unmapped — not because matching failed, but because no profile existed to map
 * them onto. The originals covered 59 notes; the catalog contains 1,623.
 *
 * These 24 were chosen purely by row count: they are the largest remaining gaps,
 * together accounting for roughly 20,000 note rows the weather engine could not
 * see. Everything here is a real material with well-established seasonal
 * character in perfumery, so each can be given a defensible climate window.
 *
 * Deliberately EXCLUDED, and worth understanding why:
 *
 *   Woody Notes, Green Notes, Floral Notes, Spices, Citruses
 *     Category labels, not materials. "Spices" spans cinnamon (warm, winter) and
 *     coriander (fresh, spring) — no single temperature window is honest.
 *
 *   Orange, Pepper
 *     Ambiguous against the existing taxonomy. "Pepper" could be Black Pepper or
 *     Pink Pepper, which already exist as separate profiles with different
 *     character. Guessing corrupts real data; both the string matcher and the
 *     embedding model proposed wrong answers for these.
 *
 * ── Provenance of the values ─────────────────────────────────────────────────
 * Temperature windows and seasons follow standard perfumery convention, checked
 * against published note references (see the research notes in each block).
 * They are editorial judgements, not measurements — the same is true of the
 * original 59. Tune them against how the recommendations actually feel; that
 * feedback is more valuable than any source.
 *
 * Usage:
 *   php artisan db:seed --class=NoteClimateProfileExpansionSeeder
 *   php artisan notes:map --apply            # re-map with the wider taxonomy
 *   php artisan notes:map --method=embed --min-confidence=0.93 --apply
 */
class NoteClimateProfileExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $notes = [

            // ── White & green florals ─────────────────────────────────────
            // Lily of the valley (muguet): light, fresh, green and airy; the
            // archetypal spring floral, strongly associated with new growth.
            [
                'name'                => 'Lily-of-the-Valley',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            // Geranium: green-rosy with minty and citrus facets. Genuinely
            // seasonless — heat amplifies the minty lift, cold leaves the rosy
            // heart intact — so it carries a wide window and a modest weight.
            [
                'name'                => 'Geranium',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 34,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.55,
            ],
            // Freesia: clean and fruity, closer to green apple than to rose.
            [
                'name'                => 'Freesia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 32,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            // Magnolia: smooth, lemony, clean — a citrus-leaning floral.
            [
                'name'                => 'Magnolia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 32,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            // Gardenia: dense, almost tropical sweetness with real projection.
            // Narrower upper bound than the lighter whites — it turns cloying
            // in heat, which is also why humidity is capped at medium.
            [
                'name'                => 'Gardenia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 15,
                'max_temp_celsius'    => 29,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Orchid',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.50,
            ],
            // Carnation: spicy, clove-adjacent floral — skews cool.
            [
                'name'                => 'Carnation',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 2,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.60,
            ],
            // Mimosa: soft, powdery, honeyed — an early-spring signature.
            [
                'name'                => 'Mimosa',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.55,
            ],
            // Osmanthus: apricot-and-leather floral, autumnal in character.
            [
                'name'                => 'Osmanthus',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],
            // Lotus: watery, translucent floral — reads as cooling.
            [
                'name'                => 'Lotus',
                'note_family'         => 'Aquatic',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.65,
            ],

            // ── Citrus & green ────────────────────────────────────────────
            [
                'name'                => 'Lime',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.75,
            ],
            // Petitgrain: distilled from bitter-orange twigs — greener and
            // drier than neroli, and holds up better in heat.
            [
                'name'                => 'Petitgrain',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            // Galbanum: sharp, bitter, intensely green resin — spring.
            [
                'name'                => 'Galbanum',
                'note_family'         => 'Green',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.60,
            ],
            // Clary sage / sage: herbal, aromatic, slightly warm.
            [
                'name'                => 'Sage',
                'note_family'         => 'Aromatic',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.50,
            ],
            // Artemisia (wormwood): bitter, dry, herbaceous.
            [
                'name'                => 'Artemisia',
                'note_family'         => 'Aromatic',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.45,
            ],
            [
                'name'                => 'Cypress',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => 5,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.50,
            ],

            // ── Fruits ────────────────────────────────────────────────────
            // Pear: crisp, watery, fresh — a transitional fruit.
            [
                'name'                => 'Pear',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.55,
            ],
            // Plum: darker, jammy, wine-like — the cool-weather stone fruit.
            [
                'name'                => 'Plum',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 0,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Apricot',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 32,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.55,
            ],
            [
                'name'                => 'Pineapple',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Coconut',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.70,
            ],

            // ── Warm spices & gourmand ────────────────────────────────────
            // Nutmeg: cosy, enveloping, autumnal — pairs with resins and woods.
            [
                'name'                => 'Nutmeg',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => -5,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.65,
            ],
            // Coriander seed: warm and spicy but with a citrus lift, so it
            // tolerates far more heat than the other spices here.
            [
                'name'                => 'Coriander',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.50,
            ],
            // Tobacco: dry, sweet, resinous — firmly cold-weather.
            [
                'name'                => 'Tobacco',
                'note_family'         => 'Oriental',
                'min_temp_celsius'    => -10,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Honey',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => -5,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Almond',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => -5,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.55,
            ],
        ];

        foreach ($notes as $note) {
            NoteClimateProfile::updateOrCreate(
                ['name' => $note['name']],
                $note
            );
        }

        $this->command->info('  Seeded ' . count($notes) . ' additional note climate profiles.');
    }
}
