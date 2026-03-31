<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

final class SivsoRbac
{
    /**
     * Nombres de rol que omiten comprobación de permisos (Gate::before + UI).
     * Incluye el slug del proyecto y el estilo "Super Admin" de la documentación Spatie.
     */
    public const SUPER_ADMIN_ROLE_NAMES = [
        'super-admin',
        'Super Admin',
    ];

    public static function userIsSuperAdmin(?Authenticatable $user): bool
    {
        if ($user === null || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole(self::SUPER_ADMIN_ROLE_NAMES);
    }
}
