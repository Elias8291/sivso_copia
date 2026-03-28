<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $wasForced = (int) $user->must_change_password === 0;

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => 1,
        ]);

        if ($wasForced) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Contraseña actualizada. Ya puedes usar el sistema.');
        }

        return back();
    }
}
