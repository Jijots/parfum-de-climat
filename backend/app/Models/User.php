<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model: User
 *
 * Represents an authenticated user of the Parfum de Climat application.
 * Users have one of two roles:
 *
 *  - 'admin' : Full CRUD access to the fragrance catalog and note climate
 *              profiles via the admin panel. Can trigger PerfumAPI imports.
 *
 *  - 'user'  : Read-only access to the catalog. Can manage their own
 *              collection and receive weather-based recommendations.
 *
 * Authentication is handled via Laravel Sanctum (token-based). The Flutter
 * app stores the token in secure storage and sends it as a Bearer token.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $role             'admin' | 'user'
 * @property string|null $avatar_path      Relative Laravel Storage path
 * @property string      $timezone         IANA timezone, e.g. "Asia/Manila"
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_path',
        'timezone',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Ensures the password hash is never exposed in API responses.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The fragrances in this user's personal collection.
     *
     * Pivot extras: personal_notes, is_favorite, acquired_date.
     * Access via $user->collection or $user->fragrances()->wherePivot(...).
     *
     * @return BelongsToMany<Fragrance>
     */
    public function fragrances(): BelongsToMany
    {
        return $this->belongsToMany(Fragrance::class, 'user_collections')
                    ->withPivot(['personal_notes', 'is_favorite', 'acquired_date'])
                    ->withTimestamps();
    }

    /**
     * All recommendation events logged for this user.
     *
     * @return HasMany<WeatherLog>
     */
    public function weatherLogs(): HasMany
    {
        return $this->hasMany(WeatherLog::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Determine if the user has the 'admin' role.
     *
     * Used by EnsureUserIsAdmin middleware and Blade @can directives.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
