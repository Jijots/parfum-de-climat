<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds social-login support to the users table.
 *
 * ── Why this exists ──────────────────────────────────────────────────────────
 * Registration sends a verification link, and the transactional email provider
 * only permits one verified sending domain per account — which is in use for
 * something else. In practice that means a stranger can register but the link
 * never reaches them, and password reset silently fails too.
 *
 * Signing in with Google sidesteps that entirely: Google has already proven the
 * address belongs to the person, so those accounts are marked verified on
 * creation and never need an email from us at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Provider name ('google') and the provider's stable user id.
            // Nullable because password accounts have neither.
            $table->string('oauth_provider')->nullable()->after('password');
            $table->string('oauth_id')->nullable()->after('oauth_provider');
            $table->string('avatar_url')->nullable()->after('avatar_path');

            // One provider account maps to at most one local user. Composite
            // rather than a bare oauth_id index: ids are only unique within a
            // provider, so a second provider could otherwise collide.
            $table->unique(['oauth_provider', 'oauth_id']);
        });

        // Password becomes nullable — an account created through Google has no
        // password, and storing a random unusable one instead would let it be
        // targeted by password reset and credential stuffing as if it were real.
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['oauth_provider', 'oauth_id']);
            $table->dropColumn(['oauth_provider', 'oauth_id', 'avatar_url']);
        });

        // Rows without a password would violate the NOT NULL constraint, so
        // they have to go before it can be restored. Down-migrating therefore
        // DELETES every OAuth-only account — acceptable for a rollback, but
        // worth knowing before running it.
        \Illuminate\Support\Facades\DB::table('users')->whereNull('password')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
