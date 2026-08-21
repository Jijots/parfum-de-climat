<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Profile', [
            'user' => [
                'name'     => $user->name,
                'email'    => $user->email,
                'gender'   => $user->gender,
                'timezone' => $user->timezone,
            ],
            'urls'      => ['update' => route('profile.update')],
            // Shipped as a list so the select has real options. ~400 entries is
            // a few KB and avoids a second round-trip just to populate a dropdown.
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'gender'   => ['nullable', 'in:masculine,feminine,unisex'],
            'timezone' => ['nullable', 'timezone'],
        ]);

        $request->user()->update([
            'name'     => $data['name'],
            'gender'   => $data['gender'] ?? null,
            'timezone' => $data['timezone'] ?? $request->user()->timezone,
        ]);

        return back()->with('status', 'profile-updated');
    }
}
