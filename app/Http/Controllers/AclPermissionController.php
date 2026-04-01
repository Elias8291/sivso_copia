<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos de la aplicación (Spatie); sincronizados con roles vía pivote.
 */
final class AclPermissionController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'created_at' => $p->created_at?->toIso8601String(),
                'updated_at' => $p->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Permissions/Create');
    }

    public function show(Permission $permission): Response
    {
        return Inertia::render('Permissions/Show', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at?->toIso8601String(),
                'updated_at' => $permission->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name', ''))]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}]+(?:[\s\.,\-]+[\p{L}\p{N}]+)*$/u',
                Rule::unique('permissions', 'name')->where('guard_name', 'web'),
            ],
        ]);

        Permission::query()->create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $this->forgetPermissionCache();

        return redirect()->route('permissions.index');
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name', ''))]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}]+(?:[\s\.,\-]+[\p{L}\p{N}]+)*$/u',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($permission->id),
            ],
        ]);

        $permission->name = $validated['name'];
        $permission->save();

        $this->forgetPermissionCache();

        return redirect()->route('permissions.index');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        $this->forgetPermissionCache();

        return redirect()->route('permissions.index');
    }

    private function forgetPermissionCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
