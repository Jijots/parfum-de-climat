<?php

namespace Database\Seeders;

use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder: NoteMappingSeeder
 *
 * Resolves the 1132 unmapped fragrance notes left after the PerfumAPI import
 * by doing two things:
 *
 *  1. INSERT new NoteClimateProfile records for genuinely distinct notes that
 *     are not covered by the original 60 profiles.
 *
 *  2. UPDATE fragrance_notes rows to point at the correct profile_id for:
 *     a) Aliases — different name/spelling for an already-profiled note
 *        (e.g. "Olibanum" → Frankincense, "Vanille" → Vanilla)
 *     b) Newly inserted profiles
 *        (e.g. "Geranium", "Tobacco", "Hyacinth")
 *
 * Safe to run multiple times — profiles use updateOrCreate, note mappings
 * use bulk UPDATE WHERE note_profile_id IS NULL to avoid overwriting any
 * manual corrections made through the admin panel.
 */
class NoteMappingSeeder extends Seeder
{
    public function run(): void
    {
        // ── Step 1: Insert new profiles ───────────────────────────────────
        $this->command->info('Inserting new note climate profiles...');
        $this->insertNewProfiles();

        // ── Step 2: Build a full name → id index ──────────────────────────
        $profileIndex = NoteClimateProfile::all()
            ->keyBy(fn ($p) => strtolower(trim($p->name)))
            ->map(fn ($p) => $p->id);

        // ── Step 3: Apply alias mappings ──────────────────────────────────
        $this->command->info('Applying alias mappings...');
        $this->applyAliases($profileIndex);

        $remaining = FragranceNote::whereNull('note_profile_id')->count();
        $this->command->info("Done. Remaining unmapped notes: {$remaining}");
    }

    // ─────────────────────────────────────────────────────────────────────
    // New Profiles
    // ─────────────────────────────────────────────────────────────────────

    private function insertNewProfiles(): void
    {
        $profiles = [

            // ── Floral ────────────────────────────────────────────────────

            [
                'name'                => 'Geranium',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 24,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Lily-Of-The-Valley',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Gardenia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Carnation',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 8,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Narcissus',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 6,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Honeysuckle',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 16,
                'max_temp_celsius'    => 28,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Orchid',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => 30,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Freesia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 24,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Mimosa',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Magnolia',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Hyacinth',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 6,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Lilac',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 22,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Osmanthus',
                'note_family'         => 'Floral',
                'min_temp_celsius'    => 14,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'autumn'],
                'climate_weight'      => 0.70,
            ],

            // ── Fruity ────────────────────────────────────────────────────

            [
                'name'                => 'Pear',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.60,
            ],
            [
                'name'                => 'Plum',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 5,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['autumn'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Pineapple',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 20,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Coconut',
                'note_family'         => 'Fruity',
                'min_temp_celsius'    => 22,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.70,
            ],
            [
                'name'                => 'Lime',
                'note_family'         => 'Citrus',
                'min_temp_celsius'    => 18,
                'max_temp_celsius'    => null,
                'humidity_preference' => 'any',
                'ideal_seasons'       => ['summer'],
                'climate_weight'      => 0.75,
            ],

            // ── Herbal / Spicy ────────────────────────────────────────────

            [
                'name'                => 'Sage',
                'note_family'         => 'Herbal',
                'min_temp_celsius'    => 10,
                'max_temp_celsius'    => 24,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['spring', 'summer', 'autumn'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Coriander',
                'note_family'         => 'Herbal',
                'min_temp_celsius'    => 12,
                'max_temp_celsius'    => 26,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['spring', 'summer'],
                'climate_weight'      => 0.65,
            ],
            [
                'name'                => 'Nutmeg',
                'note_family'         => 'Spicy',
                'min_temp_celsius'    => 3,
                'max_temp_celsius'    => 16,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
            [
                'name'                => 'Juniper',
                'note_family'         => 'Woody',
                'min_temp_celsius'    => 3,
                'max_temp_celsius'    => 18,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter', 'spring'],
                'climate_weight'      => 0.70,
            ],

            // ── Earthy / Mossy ────────────────────────────────────────────

            [
                'name'                => 'Moss',
                'note_family'         => 'Mossy',
                'min_temp_celsius'    => 4,
                'max_temp_celsius'    => 16,
                'humidity_preference' => 'high',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.70,
            ],

            // ── Aldehydic ─────────────────────────────────────────────────

            [
                'name'                => 'Aldehydes',
                'note_family'         => 'Aldehydic',
                'min_temp_celsius'    => 4,
                'max_temp_celsius'    => 20,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['autumn', 'winter', 'spring'],
                'climate_weight'      => 0.60,
            ],

            // ── Sweet / Gourmand ──────────────────────────────────────────

            [
                'name'                => 'Honey',
                'note_family'         => 'Gourmand',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 15,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],

            // ── Warm / Smoky ──────────────────────────────────────────────

            [
                'name'                => 'Tobacco',
                'note_family'         => 'Warm',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 13,
                'humidity_preference' => 'low',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.80,
            ],
            [
                'name'                => 'Rum',
                'note_family'         => 'Warm',
                'min_temp_celsius'    => null,
                'max_temp_celsius'    => 12,
                'humidity_preference' => 'medium',
                'ideal_seasons'       => ['autumn', 'winter'],
                'climate_weight'      => 0.75,
            ],
        ];

        foreach ($profiles as $data) {
            NoteClimateProfile::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('  New profiles inserted/updated: ' . count($profiles));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Alias Mappings
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Map alias note names to the correct profile.
     *
     * Each key is the raw_note_name as it appears in fragrance_notes.
     * Each value is the canonical profile name (must exist in NoteClimateProfile).
     *
     * Only rows where note_profile_id IS NULL are updated — manual admin
     * corrections are never overwritten.
     *
     * @param  \Illuminate\Support\Collection<string, int>  $index  Lower-cased name → id
     */
    private function applyAliases(\Illuminate\Support\Collection $index): void
    {
        // ── alias raw_note_name => canonical profile name ─────────────────
        $aliases = [

            // Neroli / Orange Blossom family
            'Orange Blossom'           => 'Neroli',
            'African Orange Flower'    => 'Neroli',
            'Tunisian Neroli'          => 'Neroli',
            'Tunisian Orange Blossom'  => 'Neroli',
            'Lemon Blossom'            => 'Neroli',

            // Citrus — Mandarin Orange variants
            'Orange'                   => 'Mandarin Orange',
            'Tangerine'                => 'Mandarin Orange',
            'Mandarin'                 => 'Mandarin Orange',
            'Blood Mandarin'           => 'Mandarin Orange',
            'Green Mandarin'           => 'Mandarin Orange',
            'Clementine'               => 'Mandarin Orange',
            'Bitter Orange'            => 'Mandarin Orange',
            'Blood Orange'             => 'Mandarin Orange',
            'Sicilian Orange'          => 'Mandarin Orange',
            'California Orange'        => 'Mandarin Orange',
            'Italian Orange'           => 'Mandarin Orange',

            // Citrus — Lemon variants
            'Citruses'                 => 'Lemon',
            'Citron'                   => 'Lemon',
            'Sicilian Lemon'           => 'Lemon',
            'Italian Lemon'            => 'Lemon',
            'Amalfi Lemon'             => 'Lemon',
            'Lemon Zest'               => 'Lemon',
            'Lemon Verbena'            => 'Lemon',

            // Citrus — Grapefruit
            'Pomelo'                   => 'Grapefruit',
            'Yuzu'                     => 'Grapefruit',

            // Citrus — Bergamot
            'Calabrian Bergamot'       => 'Bergamot',
            'Petitgrain'               => 'Bergamot',

            // Citrus — Lime (now its own profile)
            'Citrus'                   => 'Lime',
            'Chinotto'                 => 'Lime',

            // Rose variants
            'Bulgarian Rose'           => 'Rose',
            'Turkish Rose'             => 'Rose',
            'Damask Rose'              => 'Rose',
            'May Rose'                 => 'Rose',
            'Rose De Mai'              => 'Rose',
            'Rose Oil'                 => 'Rose',
            'White Rose'               => 'Rose',
            'Black Rose'               => 'Rose',
            'Grasse Rose'              => 'Rose',
            'Taif Rose'                => 'Rose',
            'Rose Hip'                 => 'Rose',

            // Jasmine variants
            'Jasmine Sambac'           => 'Jasmine',
            'Indian Jasmine'           => 'Jasmine',
            'Egyptian Jasmine'         => 'Jasmine',
            'Moroccan Jasmine'         => 'Jasmine',
            'Chinese Jasmine'          => 'Jasmine',

            // Tuberose
            'Indian Tuberose'          => 'Tuberose',

            // Iris / Orris
            'Orris'                    => 'Orris Root',
            'Violet Root'              => 'Iris',

            // Generic floral
            'Flowers'                  => 'Peony',
            'Floral Notes'             => 'Peony',
            'White Flowers'            => 'Jasmine',
            'Fruity Notes'             => 'Peach',
            'Tropical Fruits'          => 'Pineapple',
            'Sweet Pea'                => 'Heliotrope',
            'Cyclamen'                 => 'Heliotrope',

            // Hyacinth (various spellings)
            'Hiacynth'                 => 'Hyacinth',
            'Blue Hyacinth'            => 'Hyacinth',
            'Water Hyacinth'           => 'Hyacinth',

            // Cedar variants
            'Virginia Cedar'           => 'Cedar',
            'Virginian Cedar'          => 'Cedar',
            'Atlas Cedar'              => 'Cedar',
            'Cedarwood'                => 'Cedar',
            'Cedar Essence'            => 'Cedar',
            'Texas Cedar'              => 'Cedar',
            'Red Cedar'                => 'Cedar',
            'Cypress'                  => 'Cedar',
            'Italian Cypress'          => 'Cedar',
            'Sycamore'                 => 'Cedar',
            'Birch'                    => 'Cedar',
            'Dry Wood'                 => 'Cedar',
            'Woody Notes'              => 'Cedar',
            'Woodsy Notes'             => 'Cedar',

            // Sandalwood variants
            'Brazilian Rosewood'       => 'Sandalwood',
            'Palisander Rosewood'      => 'Sandalwood',
            'Cashmeran'                => 'Sandalwood',
            'Cashmere Wood'            => 'Sandalwood',
            'Cashmirwood'              => 'Sandalwood',
            'White Woods'              => 'Sandalwood',
            'Teak Wood'                => 'Sandalwood',
            'Mahogany'                 => 'Sandalwood',
            'Amyris'                   => 'Sandalwood',
            'Precious Woods'           => 'Sandalwood',
            'Sandalowood'              => 'Sandalwood', // typo fix

            // Guaiac Wood
            'Oak'                      => 'Guaiac Wood',
            'Papyrus'                  => 'Guaiac Wood',
            'Akigalawood'              => 'Guaiac Wood',

            // Oud
            'Agarwood (Oud)'           => 'Oud',

            // Frankincense / Incense
            'Olibanum'                 => 'Frankincense',
            'Incense'                  => 'Frankincense',
            'Elemi'                    => 'Frankincense',
            'Elemi Resin'              => 'Frankincense',
            'Fir Resin'                => 'Frankincense',
            'Kyara Incense'            => 'Frankincense',

            // Myrrh
            'Opoponax'                 => 'Myrrh',
            'Myrhh'                    => 'Myrrh', // typo fix

            // Benzoin / Balsam / Resinous
            'Siam Benzoin'             => 'Benzoin',
            'Peru Balsam'              => 'Benzoin',
            'Tolu Balsam'              => 'Benzoin',
            'Gurjan Balsam'            => 'Benzoin',
            'Canada Balsam'            => 'Benzoin',
            'Styrax'                   => 'Benzoin',
            'Resins'                   => 'Benzoin',
            'Balsam Fir'               => 'Benzoin',

            // Labdanum
            'French Labdanum'          => 'Labdanum',
            'Spanish Labdanum'         => 'Labdanum',

            // Amber
            'Amberwood'                => 'Amber',
            'Oriental Notes'           => 'Amber',

            // Musk variants
            'Ambrette'                 => 'Musk',
            'Ambrette (Musk Mallow)'   => 'Musk',
            'Animal Notes'             => 'Musk',
            'Civet'                    => 'Musk',
            'Synthetic Civet'          => 'Musk',
            'Lactones'                 => 'Musk',

            // Ambergris
            'Ambroxan'                 => 'Ambergris',
            'Ambrofix™'                => 'Ambergris',

            // Vetiver variants
            'Haitian Vetiver'          => 'Vetiver',
            'Madagascar Vetiver'       => 'Vetiver',
            'Tahitian Vetiver'         => 'Vetiver',
            'Java Vetiver Oil'         => 'Vetiver',
            'Vetyver'                  => 'Vetiver', // alt spelling

            // Patchouli
            'Indonesian Patchouli Leaf' => 'Patchouli',

            // Oakmoss
            'Oak Moss'                 => 'Oakmoss',

            // Blackcurrant
            'Black Currant'            => 'Blackcurrant',
            'Cassis'                   => 'Blackcurrant',
            'Red Currant'              => 'Blackcurrant',

            // Raspberry / Red fruits
            'Blackberry'               => 'Raspberry',
            'Red Berries'              => 'Raspberry',
            'Strawberry'               => 'Raspberry',
            'Red Apple'                => 'Raspberry',
            'Green Apple'              => 'Apple',
            'Sour Cherry'              => 'Raspberry',
            'Cherry'                   => 'Raspberry',
            'Black Cherry'             => 'Raspberry',
            'Cherry Liqueur'           => 'Raspberry',
            'Red Currant'              => 'Raspberry',
            'Blueberry'                => 'Raspberry',
            'Pomegranate'              => 'Raspberry',

            // Peach / Stone fruit
            'Apricot'                  => 'Peach',
            'Nectarine'                => 'Peach',
            'Passionfruit'             => 'Peach',
            'Litchi'                   => 'Peach',
            'Mango'                    => 'Pineapple',
            'Green Mango'              => 'Pineapple',
            'Melon'                    => 'Peach',
            'Quince'                   => 'Peach',
            'Plum'                     => 'Plum',

            // Vanilla variants
            'Vanille'                  => 'Vanilla',
            'Madagascar Vanilla'       => 'Vanilla',
            'Bourbon Vanilla'          => 'Vanilla',
            'Vanilla Bean'             => 'Vanilla',
            'Vanila'                   => 'Vanilla', // typo fix
            'Black Vanilla Husk'       => 'Vanilla',

            // Tonka Bean
            'Tonka'                    => 'Tonka Bean',
            'Coumarin'                 => 'Tonka Bean',

            // Chocolate variants
            'Cacao'                    => 'Chocolate',
            'Dark Chocolate'           => 'Chocolate',
            'White Chocolate'          => 'Chocolate',
            'Mexican Chocolate'        => 'Chocolate',

            // Coffee / Roasted
            'Roasted Coffee Beans'     => 'Coffee',
            'Mocha'                    => 'Coffee',

            // Caramel / Sweet
            'Sugar'                    => 'Caramel',
            'Brown Sugar'              => 'Caramel',
            'Sugar Cane'               => 'Caramel',
            'Marshmallow'              => 'Caramel',
            'Toffee'                   => 'Caramel',
            'Cotton Candy'             => 'Caramel',
            'Biscuit'                  => 'Caramel',
            'Candied Almond'           => 'Caramel',
            'Candied Fruits'           => 'Caramel',
            'Dried Fruits'             => 'Caramel',
            'Dates'                    => 'Caramel',
            'Creme Brulee'             => 'Caramel',

            // Praline / Nuts
            'Hazelnut'                 => 'Praline',
            'Pistachio'                => 'Praline',
            'Chestnut'                 => 'Praline',
            'Almond'                   => 'Praline',
            'Bitter Almond'            => 'Praline',
            'Almond Milk'              => 'Praline',
            'White Almond'             => 'Praline',
            'Almond Blossom'           => 'Praline',
            'Almond Tree'              => 'Praline',

            // Pepper variants
            'Pepper'                   => 'Black Pepper',
            'White Pepper'             => 'Black Pepper',
            'Madagascar Pepper'        => 'Black Pepper',
            'Sichuan Pepper'           => 'Black Pepper',
            'Red Pepper'               => 'Black Pepper',
            'Pimento'                  => 'Black Pepper',
            'Pimento Leaf'             => 'Black Pepper',
            'Pepperwood™'              => 'Black Pepper',
            'Paprika'                  => 'Black Pepper',

            // Clove variants
            'Cloves'                   => 'Clove',

            // Cardamom
            'Guatemalan Cardamom'      => 'Cardamom',
            'Spices'                   => 'Cardamom',
            'Spicy Notes'              => 'Cardamom',

            // Cinnamon
            'Ceylon Cinnamon'          => 'Cinnamon',
            'Cassia'                   => 'Cinnamon',
            'Star Anise'               => 'Cinnamon',
            'Anise'                    => 'Cinnamon',
            'Licorice'                 => 'Cinnamon',
            'Caraway'                  => 'Cinnamon',
            'Tarragon'                 => 'Cinnamon',
            'Mace'                     => 'Cinnamon',

            // Lavender family
            'Clary Sage'               => 'Lavender',
            'Artemisia'                => 'Lavender',
            'Wormwood'                 => 'Lavender',
            'Lavender Extract'         => 'Lavender',
            'Liatris'                  => 'Lavender',

            // Rosemary / Herbal
            'Thyme'                    => 'Rosemary',
            'Red Thyme'                => 'Rosemary',
            'Marjoram'                 => 'Rosemary',
            'Oregano'                  => 'Rosemary',
            'Hyssop'                   => 'Rosemary',
            'Bay Leaf'                 => 'Rosemary',
            'Artemisia'                => 'Rosemary',
            'Juniper Berries'          => 'Juniper',

            // Ginger variants
            'Nigerian Ginger'          => 'Ginger',

            // Saffron
            'Turmeric'                 => 'Saffron',

            // Mint
            'Vervain'                  => 'Mint',

            // Tobacco family (mapped to new Tobacco profile)
            'Tobacco Leaf'             => 'Tobacco',
            'Tobacco Blossom'          => 'Tobacco',
            'White Tobacco'            => 'Tobacco',

            // Rum / Alcohol family
            'Cognac'                   => 'Rum',
            'Whiskey'                  => 'Rum',
            'Brandy'                   => 'Rum',
            'Rum'                      => 'Rum',

            // Green / Herbal
            'Green Notes'              => 'Grass',
            'Green Leaves'             => 'Grass',
            'Hay'                      => 'Grass',
            'Ivy'                      => 'Grass',
            'Galbanum'                 => 'Grass',
            'Myrtle'                   => 'Grass',
            'Fir'                      => 'Grass',
            'Broom'                    => 'Grass',
            'Pine Tree Needles'        => 'Grass',

            // Aquatic / Marine
            'Water Notes'              => 'Marine',
            'Watery Notes'             => 'Marine',
            'Sea Notes'                => 'Marine',
            'Calone'                   => 'Marine',
            'Seaweed'                  => 'Marine',
            'Mineral Notes'            => 'Marine',
            'Aquozone'                 => 'Aquatic Notes',
            'Salt'                     => 'Sea Salt',

            // Green Tea variants
            'Matcha Tea'               => 'Green Tea',
            'Chinese Black Tea'        => 'Green Tea',
            'Jasmine Tea'              => 'Green Tea',
            'Mate'                     => 'Green Tea',

            // Milk / Lactonic
            'Milk'                     => 'Caramel',
            'Coconut Milk'             => 'Coconut',
            'Whipped Cream'            => 'Caramel',
            'Rice'                     => 'Caramel',
            'Kulfi'                    => 'Caramel',
            'Dragée'                   => 'Caramel',

            // Smoke / Leather
            'Smoke'                    => 'Leather',
            'Ink'                      => 'Leather',

            // Iris / Violet family
            'Powdery Notes'            => 'Iris',

            // Fig (maps to Grass — green facet)
            'Fig'                      => 'Grass',
            'Fig Leaf'                 => 'Grass',
            'Olive Tree'               => 'Grass',
            'Olive Blossom'            => 'Grass',

            // Misc woody/balsamic
            'Davana'                   => 'Sandalwood',
            'Cypriol Oil Or Nagarmotha' => 'Vetiver',
            'Elemi'                    => 'Frankincense',
            'Truffle'                  => 'Vetiver',
            'Hedione'                  => 'Jasmine',
            'Mahonial'                 => 'Sandalwood',

            // Beeswax / Waxy
            'Beeswax'                  => 'Honey',
            'Honey'                    => 'Honey',
            'White Honey'              => 'Honey',

            // Carnation variants
            'Indonesian Carnation'     => 'Carnation',

            // Nutmeg variants
            'Indonesian Nutmeg'        => 'Nutmeg',
            'Nutmeg Flower'            => 'Nutmeg',

            // Geranium variants
            'Bourbon Geranium'         => 'Geranium',
            'Pelargonium'              => 'Geranium',

            // Misc floral mappings
            'Marigold'                 => 'Carnation',
            'Lotus'                    => 'Peony',
            'Amaryllis'                => 'Peony',
            'Camellia'                 => 'Peony',
            'Champaca'                 => 'Gardenia',
            'Datura'                   => 'Gardenia',
            'Tiare Flower'             => 'Gardenia',
            'Snowdrops'                => 'Lily-Of-The-Valley',
            'Lily'                     => 'Lily-Of-The-Valley',
            'Lemon Blossom'            => 'Lily-Of-The-Valley',
            'Tulip'                    => 'Lily-Of-The-Valley',
            'Clover'                   => 'Lily-Of-The-Valley',
            'Mock Orange'              => 'Neroli',
            'Peach Blossom'            => 'Peach',
            'Orange Leaf'              => 'Petitgrain', // will resolve via bergamot

            // Powdery / Gourmand
            'Gourmand Accord'          => 'Caramel',
            'Powdery Notes'            => 'Iris',

            // Misc 1-occurrence leftovers
            'Carrot'                   => 'Iris',
            'Carrot Seeds'             => 'Iris',
            'Angelica'                 => 'Iris',
            'Mastic Or Lentisque'      => 'Benzoin',
            'Lemon Verbena'            => 'Lemon',
            'Chamomile'                => 'Lavender',
            'Immortelle'               => 'Lavender',
            'Wormwood'                 => 'Lavender',
            'Marjoram'                 => 'Rosemary',
            'Soup'                     => 'Grass',
            'Soap'                     => 'Aldehydes',
            'Birch'                    => 'Cedar',
            'Oak'                      => 'Guaiac Wood',
            'Cade Oil'                 => 'Vetiver',
            'Cypriol Oil Or Nagarmotha' => 'Vetiver',
            'Carambola (Star Fruit)'   => 'Pineapple',
            'Rhubarb'                  => 'Raspberry',
            'Rhuburb'                  => 'Raspberry', // typo
            'Papyrus'                  => 'Guaiac Wood',
            'Amyl Salicylate'          => 'Jasmine',
            'Mignonette'               => 'Lily-Of-The-Valley',
            'Narcissus'                => 'Narcissus',

            // Resin / Wood misc
            'Cypriol Oil Or Nagarmotha' => 'Vetiver',
            'Palisander Rosewood'      => 'Sandalwood',
            'Cashmirwood'              => 'Sandalwood',
            'White Nerium Oleander'    => 'Gardenia',
            'Black Locust'             => 'Lily-Of-The-Valley',
            'Black Elder'              => 'Lily-Of-The-Valley',
        ];

        $mapped   = 0;
        $skipped  = 0;

        foreach ($aliases as $rawName => $canonicalName) {
            $profileId = $index->get(strtolower($canonicalName));

            if (! $profileId) {
                $this->command->warn("  Profile not found for alias target: '{$canonicalName}' (raw: '{$rawName}')");
                $skipped++;
                continue;
            }

            $updated = DB::table('fragrance_notes')
                ->where('raw_note_name', $rawName)
                ->whereNull('note_profile_id')
                ->update(['note_profile_id' => $profileId]);

            $mapped += $updated;
        }

        $this->command->info("  Notes mapped via aliases: {$mapped}");
        if ($skipped > 0) {
            $this->command->warn("  Aliases skipped (profile not found): {$skipped}");
        }
    }
}
