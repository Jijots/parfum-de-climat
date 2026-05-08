<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 *
 * Master seeder. Runs all project seeders in the correct dependency order:
 *  1. NoteClimateProfileSeeder — must run before any import command so that
 *     note-to-profile matching works on the first import.
 *  2. Default admin user — created for initial admin panel access.
 *
 * Run with: php artisan db:seed
 * Or fresh migration + seed: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── 1. Note Climate Profiles ──────────────────────────────────────
        // MUST run first — the fragrances:import command uses these profiles
        // to auto-map note names on import.
        $this->call(NoteClimateProfileSeeder::class);

        // ── 2. Default Admin User ─────────────────────────────────────────
        // Creates the initial admin account for the admin panel.
        // CHANGE THESE CREDENTIALS before deploying to any shared environment.
        User::firstOrCreate(
            ['email' => 'admin@parfumdeclimat.app'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),  // Change before production
                'role'     => 'admin',
                'timezone' => 'Asia/Manila',
            ]
        );

        $this->command->info('  Created default admin: admin@parfumdeclimat.app / password');
        $this->command->warn('  ⚠ Change the admin password before deploying to production.');
    }
}
