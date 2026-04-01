<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'rfc', 'nue', 'email', 'password', 'activo', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        if ($this->rfc === null) {
            return false;
        }

        return in_array($this->rfc, config('sivso.super_admin_rfcs', []), true);
    }

    /**
     * Rol sembrado como administrador del sistema (todos los módulos).
     * Debe coincidir con el nombre en RolePermissionSeeder / pantalla de roles.
     */
    public function isSivsoAdministrator(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->can($slug);
    }

    /**
     * @return list<string>
     */
    public function permissionsForInertia(): array
    {
        if ($this->isSuperAdmin() || $this->isSivsoAdministrator()) {
            return array_values(config('sivso.all_permissions', []));
        }

        return $this->getAllPermissions()->pluck('name')->unique()->values()->all();
    }
}
