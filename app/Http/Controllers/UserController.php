<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles')->select('id', 'name', 'rfc', 'nue', 'email', 'activo', 'created_at')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'rfc' => $user->rfc,
                    'nue' => $user->nue,
                    'email' => $user->email,
                    'activo' => (bool) $user->activo,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at,
                ];
            });

        $roles = Role::orderBy('name')->pluck('name');

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:20', 'unique:users,rfc'],
            'nue' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'activo' => ['boolean'],
            'roles' => ['array'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'rfc' => strtoupper($validated['rfc']),
            'nue' => $validated['nue'] ? strtoupper($validated['nue']) : null,
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'activo' => $validated['activo'] ?? true,
            'must_change_password' => 0,
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'rfc' => $user->rfc,
                'nue' => $user->nue,
                'email' => $user->email,
                'activo' => (bool) $user->activo,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
            ]
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'nue' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'activo' => ['boolean'],
            'roles' => ['array'],
        ]);

        $user->name = $validated['name'];
        $user->rfc = strtoupper($validated['rfc']);
        $user->nue = $validated['nue'] ? strtoupper($validated['nue']) : null;
        $user->email = strtolower($validated['email']);
        $user->activo = $validated['activo'] ?? true;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->must_change_password = 0;
        }

        $user->save();

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
