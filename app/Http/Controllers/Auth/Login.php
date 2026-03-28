<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Login extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'rfc' => ['required', 'string', 'max:20'],
            'password' => ['required'],
        ]);

        $user = User::where('rfc', strtoupper($credentials['rfc']))->first();

        if (! $user) {
            return back()
                ->withErrors(['rfc' => 'El RFC no existe.'])
                ->onlyInput('rfc');
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['password' => 'La contrasena es incorrecta.'])
                ->onlyInput('rfc');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function __invoke(Request $request): RedirectResponse
    {
        return $this->login($request);
    }
}
