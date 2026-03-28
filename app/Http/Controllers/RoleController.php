<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions_count' => $role->permissions->count(),
                    'permission_ids' => $role->permissions->pluck('id')->values()->all(),
                    'created_at' => $role->created_at,
                ];
            });

        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => strtoupper($validated['name'])]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function show(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]),
                'created_at' => $role->created_at,
            ]
        ]);
    }

    public function edit(Role $role): Response
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);
        $role->load('permissions');

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id')->toArray(),
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => strtoupper($validated['name'])]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
