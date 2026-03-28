<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::orderBy('name')->get()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at,
            ];
        });

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Permissions/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => strtolower($validated['name'])]);

        return redirect()->route('permissions.index')->with('success', 'Permiso creado exitosamente.');
    }

    public function show(Permission $permission): Response
    {
        return Inertia::render('Permissions/Show', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at,
            ]
        ]);
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
            ]
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
        ]);

        $permission->update(['name' => strtolower($validated['name'])]);

        return redirect()->route('permissions.index')->with('success', 'Permiso actualizado exitosamente.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permiso eliminado exitosamente.');
    }
}
