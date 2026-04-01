<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class AdminUserController extends Controller
{
    public function index(): Response
    {
        $roleNames = Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name')->values()->all();

        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $u) => $this->userPayload($u));

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roleNames,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function show(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('Users/Show', [
            'user' => $this->userPayload($user),
        ]);
    }

    public function edit(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $roleNames = Role::query()->where('guard_name', 'web')->pluck('name')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nue' => ['nullable', 'string', 'max:15'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'activo' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($roleNames)],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rfc' => $validated['rfc'] ?? null,
            'nue' => $validated['nue'] ?? null,
            'password' => Hash::make($validated['password']),
            'activo' => $validated['activo'] ?? true,
            'must_change_password' => false,
        ]);

        $user->syncRoles($validated['roles'] ?? []);
        $this->forgetPermissionCache();

        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roleNames = Role::query()->where('guard_name', 'web')->pluck('name')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nue' => ['nullable', 'string', 'max:15'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'activo' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($roleNames)],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->rfc = $validated['rfc'] ?? null;
        $user->nue = $validated['nue'] ?? null;
        $user->activo = $validated['activo'] ?? true;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->syncRoles($validated['roles'] ?? []);
        $this->forgetPermissionCache();

        return redirect()->route('users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->withErrors(['user' => 'No puedes eliminar tu propia cuenta.']);
        }

        $user->delete();
        $this->forgetPermissionCache();

        return redirect()->route('users.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'rfc' => $u->rfc,
            'nue' => $u->nue,
            'activo' => (bool) $u->activo,
            'roles' => $u->roles->pluck('name')->values()->all(),
            'created_at' => $u->created_at?->toIso8601String(),
            'email_verified_at' => $u->email_verified_at?->toIso8601String(),
        ];
    }

    private function forgetPermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
