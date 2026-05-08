<?php

namespace Tests\Feature;

use App\Models\Fragrance;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WebAuthAndGuestWardrobeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_fragrance_to_a_temporary_wardrobe(): void
    {
        $fragrance = $this->createFragrance();

        $this->postJson(route('wardrobe.toggle', $fragrance->id))
            ->assertOk()
            ->assertJson([
                'in_wardrobe' => true,
                'guest' => true,
            ]);

        $this->assertSame([
            [
                'fragrance_id' => $fragrance->id,
                'is_favorite' => false,
            ],
        ], collect(session('guest_wardrobe.items'))
            ->map(fn ($item) => [
                'fragrance_id' => $item['fragrance_id'],
                'is_favorite' => $item['is_favorite'],
            ])->all());

        $this->get(route('wardrobe'))
            ->assertOk()
            ->assertSee('temporary wardrobe')
            ->assertSee($fragrance->name);
    }

    public function test_guest_wardrobe_is_merged_into_a_verified_user_on_login(): void
    {
        $fragrance = $this->createFragrance();
        $user = User::factory()->create();

        $this->withSession([
            'guest_wardrobe.items' => [[
                'fragrance_id' => $fragrance->id,
                'is_favorite' => true,
                'added_at' => now()->toIso8601String(),
            ]],
        ])->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('app'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_collections', [
            'user_id' => $user->id,
            'fragrance_id' => $fragrance->id,
            'is_favorite' => true,
        ]);
        $this->assertNull(session('guest_wardrobe.items'));
    }

    public function test_registration_sends_verification_and_merges_guest_wardrobe(): void
    {
        Notification::fake();

        $fragrance = $this->createFragrance();

        $response = $this->withSession([
            'guest_wardrobe.items' => [[
                'fragrance_id' => $fragrance->id,
                'is_favorite' => false,
                'added_at' => now()->toIso8601String(),
            ]],
        ])->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'StrongPass!234',
            'password_confirmation' => 'StrongPass!234',
            'timezone' => 'UTC',
        ]);

        $user = User::where('email', 'new@example.com')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_collections', [
            'user_id' => $user->id,
            'fragrance_id' => $fragrance->id,
        ]);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_is_redirected_to_verification_notice_and_can_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(30),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email),
                ]
            ))
            ->assertRedirect(route('app'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    private function createFragrance(): Fragrance
    {
        return Fragrance::create([
            'name' => 'Test Fragrance',
            'brand' => 'Test House',
            'gender_target' => 'unisex',
            'external_source' => 'manual',
            'is_active' => true,
        ]);
    }
}
