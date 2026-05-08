<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('app.profile', ['user' => $request->user()]);
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
