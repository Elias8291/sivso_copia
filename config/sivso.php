<?php

return [
    'ejercicio_actual' => (int) env('SIVSO_EJERCICIO', 2026),

    /**
     * RFC con acceso total (mismo criterio que el front: is_super_admin).
     *
     * @var list<string>
     */
    'super_admin_rfcs' => array_values(array_filter(array_map(
        trim(...),
        explode(',', (string) env('SIVSO_SUPER_ADMIN_RFCS', 'XAXX010101000'))
    ))),

    /**
     * Nombres antiguos (inglés / técnico) que el RolePermissionSeeder elimina al sembrar
     * para evitar duplicados al pasar a permisos en español.
     *
     * @var list<string>
     */
    'legacy_permission_names' => [
        'delegation.self',
        'empleados.view',
        'productos.view',
        'partidas.view',
        'partidas-especificas.view',
        'dependencias.view',
        'delegaciones.view',
        'delegados.view',
        'periodos.view',
        'users.view',
        'roles.view',
        'permissions.view',
    ],

    /**
     * Permisos del sistema (Spatie `name`, guard web): textos en español claros para roles y UI.
     * Deben coincidir con `permission:...` en rutas y con `can('...')` en el front.
     * Super admin (RFC en super_admin_rfcs) pasa cualquier comprobación de Gate/permiso.
     *
     * @var list<string>
     */
    'all_permissions' => [
        'Ver mi delegación',
        'Ver empleados',
        'Crear empleados',
        'Editar empleados',
        'Eliminar empleados',
        'Ver productos',
        'Crear productos',
        'Activar productos',
        'Ver partidas',
        'Crear partidas',
        'Editar partidas',
        'Eliminar partidas',
        'Ver partidas específicas',
        'Crear partidas específicas',
        'Editar partidas específicas',
        'Eliminar partidas específicas',
        'Exportar partidas específicas',
        'Ver dependencias',
        'Eliminar dependencias',
        'Ver delegaciones',
        'Eliminar delegaciones',
        'Ver delegados',
        'Crear delegados',
        'Editar delegados',
        'Desasociar usuario de delegado',
        'Ver periodos',
        'Crear periodos',
        'Editar periodos',
        'Eliminar periodos',
        'Cerrar periodos',
        'Reabrir periodos',
        'Ver usuarios',
        'Crear usuarios',
        'Editar usuarios',
        'Eliminar usuarios',
        'Ver roles',
        'Crear roles',
        'Editar roles',
        'Eliminar roles',
        'Ver permisos del sistema',
        'Crear permisos del sistema',
        'Editar permisos del sistema',
        'Eliminar permisos del sistema',
    ],
];
