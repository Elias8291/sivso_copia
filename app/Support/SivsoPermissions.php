<?php

namespace App\Support;

use ReflectionClass;

/**
 * Nombres de permisos Spatie (guard web) alineados a rutas y módulos del sidebar.
 */
final class SivsoPermissions
{
    public const DELEGATION_SELF = 'delegation.self';

    public const EMPLEADOS_VIEW = 'empleados.view';

    public const EMPLEADOS_CREATE = 'empleados.create';

    public const EMPLEADOS_UPDATE = 'empleados.update';

    public const EMPLEADOS_DELETE = 'empleados.delete';

    public const PRODUCTOS_VIEW = 'productos.view';

    public const PRODUCTOS_CREATE = 'productos.create';

    public const PRODUCTOS_ACTIVATE = 'productos.activate';

    public const PARTIDAS_VIEW = 'partidas.view';

    public const PARTIDAS_CREATE = 'partidas.create';

    public const PARTIDAS_UPDATE = 'partidas.update';

    public const PARTIDAS_DELETE = 'partidas.delete';

    public const PARTIDAS_ESPECIFICAS_VIEW = 'partidas-especificas.view';

    public const PARTIDAS_ESPECIFICAS_EXPORT = 'partidas-especificas.export';

    public const PARTIDAS_ESPECIFICAS_CREATE = 'partidas-especificas.create';

    public const PARTIDAS_ESPECIFICAS_UPDATE = 'partidas-especificas.update';

    public const PARTIDAS_ESPECIFICAS_DELETE = 'partidas-especificas.delete';

    public const DEPENDENCIAS_VIEW = 'dependencias.view';

    public const DEPENDENCIAS_DELETE = 'dependencias.delete';

    public const DELEGACIONES_VIEW = 'delegaciones.view';

    public const DELEGACIONES_DELETE = 'delegaciones.delete';

    public const DELEGADOS_VIEW = 'delegados.view';

    public const DELEGADOS_CREATE = 'delegados.create';

    public const DELEGADOS_MANAGE_USERS = 'delegados.manage-users';

    public const PERIODOS_VIEW = 'periodos.view';

    public const PERIODOS_CREATE = 'periodos.create';

    public const PERIODOS_UPDATE = 'periodos.update';

    public const PERIODOS_DELETE = 'periodos.delete';

    public const PERIODOS_CERRAR_REABRIR = 'periodos.cerrar-reabrir';

    public const REPORTES_DELEGADOS = 'reportes.delegados';

    public const REPORTES_EMPLEADOS_PRODUCTOS = 'reportes.empleados-productos';

    public const USERS_VIEW = 'users.view';

    public const USERS_CREATE = 'users.create';

    public const USERS_UPDATE = 'users.update';

    public const USERS_DELETE = 'users.delete';

    public const ROLES_VIEW = 'roles.view';

    public const ROLES_CREATE = 'roles.create';

    public const ROLES_UPDATE = 'roles.update';

    public const ROLES_DELETE = 'roles.delete';

    public const PERMISSIONS_VIEW = 'permissions.view';

    public const PERMISSIONS_CREATE = 'permissions.create';

    public const PERMISSIONS_UPDATE = 'permissions.update';

    public const PERMISSIONS_DELETE = 'permissions.delete';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $out = [];
        foreach ((new ReflectionClass(self::class))->getReflectionConstants() as $const) {
            if (! $const->isPublic()) {
                continue;
            }
            $v = $const->getValue();
            if (is_string($v)) {
                $out[] = $v;
            }
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    public static function administrador(): array
    {
        return array_values(array_filter(
            self::all(),
            static fn (string $p) => ! str_starts_with($p, 'roles.')
                && ! str_starts_with($p, 'permissions.')
        ));
    }

    /**
     * @return list<string>
     */
    public static function gestorCatalogo(): array
    {
        return [
            self::EMPLEADOS_VIEW,
            self::EMPLEADOS_CREATE,
            self::EMPLEADOS_UPDATE,
            self::EMPLEADOS_DELETE,
            self::PRODUCTOS_VIEW,
            self::PRODUCTOS_CREATE,
            self::PRODUCTOS_ACTIVATE,
            self::PARTIDAS_VIEW,
            self::PARTIDAS_CREATE,
            self::PARTIDAS_UPDATE,
            self::PARTIDAS_DELETE,
            self::PARTIDAS_ESPECIFICAS_VIEW,
            self::PARTIDAS_ESPECIFICAS_EXPORT,
            self::PARTIDAS_ESPECIFICAS_CREATE,
            self::PARTIDAS_ESPECIFICAS_UPDATE,
            self::PARTIDAS_ESPECIFICAS_DELETE,
            self::DEPENDENCIAS_VIEW,
            self::DELEGACIONES_VIEW,
            self::DELEGADOS_VIEW,
            self::DELEGADOS_CREATE,
            self::DELEGADOS_MANAGE_USERS,
            self::PERIODOS_VIEW,
            self::REPORTES_DELEGADOS,
            self::REPORTES_EMPLEADOS_PRODUCTOS,
        ];
    }

    /**
     * @return list<string>
     */
    public static function consulta(): array
    {
        return [
            self::EMPLEADOS_VIEW,
            self::PRODUCTOS_VIEW,
            self::PARTIDAS_VIEW,
            self::PARTIDAS_ESPECIFICAS_VIEW,
            self::DEPENDENCIAS_VIEW,
            self::DELEGACIONES_VIEW,
            self::DELEGADOS_VIEW,
            self::PERIODOS_VIEW,
            self::REPORTES_DELEGADOS,
            self::REPORTES_EMPLEADOS_PRODUCTOS,
        ];
    }

    /**
     * @return list<string>
     */
    public static function delegado(): array
    {
        return [
            self::DELEGATION_SELF,
            self::REPORTES_DELEGADOS,
        ];
    }
}
