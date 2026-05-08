<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_users_table
 *
 * Creates the central users table and the two auxiliary Laravel auth tables
 * (password_reset_tokens, sessions). This is the project's identity backbone.
 *
 * Extensions over Laravel's default scaffold:
 *  - `role`        : Enum-based access control for the admin panel.
 *                    'admin' → full CRUD on the fragrance catalog.
 *                    'user'  → own collection + recommendations only.
 *  - `timezone`    : IANA string (e.g. "Asia/Manila"). Used server-side to
 *                    derive the user's current season, accounting for
 *                    Southern Hemisphere reversed seasons correctly.
 *  - `avatar_path` : Relative path in Laravel Storage for profile images.
 *  - `softDeletes` : Preserves user data on account deactivation.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ── Authentication & Identity ─────────────────────────────────
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // ── Access Control ────────────────────────────────────────────
            $table->enum('role', ['admin', 'user'])->default('user');

            // ── Profile ───────────────────────────────────────────────────
            $table->string('avatar_path')->nullable();

            // Defaults to UTC; updated by the Flutter app on first login
            // using the device's reported timezone string.
            $table->string('timezone')->default('UTC');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
