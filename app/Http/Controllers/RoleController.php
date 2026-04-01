<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RoleController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Permission $p) => ['id' => $p->id, 'name' => $p->name])
            ->values()
            ->all();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->with('permissions:id')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions_count' => $r->permissions_count,
                'permission_ids' => $r->permissions->pluck('id')->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create', [
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Role $role): Response
    {
        $role->load(['permissions:id,name']);

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn (Permission $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values()->all(),
                'created_at' => $role->created_at?->toIso8601String(),
                'updated_at' => $role->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions:id');

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id')->values()->all(),
            ],
            'permissions' => Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $name = mb_strtoupper(trim($validated['name']));

        $role = Role::query()->create([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        $this->forgetPermissionCache();

        return redirect()->route('roles.index');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->name = mb_strtoupper(trim($validated['name']));
        $role->save();

        $role->syncPermissions($validated['permissions'] ?? []);
        $this->forgetPermissionCache();

        return redirect()->route('roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (mb_strtoupper($role->name) === 'ADMINISTRADOR') {
            return back()->withErrors(['role' => 'No se puede eliminar el rol Administrador.']);
        }

        $role->delete();
        $this->forgetPermissionCache();

        return redirect()->route('roles.index');
    }

    private function forgetPermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
