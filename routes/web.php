<?php

use App\Http\Controllers\AclPermissionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegacionController;
use App\Http\Controllers\DelegadoController;
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\MyDelegationController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\PartidaEspecificaController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'password.changed'])
    ->name('dashboard');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:Ver mi delegación')->group(function () {
        Route::get('/mi-delegacion', [MyDelegationController::class, 'index'])->name('my-delegation.index');
        Route::post('/mi-delegacion/tallas', [MyDelegationController::class, 'saveTallas'])->name('my-delegation.save-tallas');
        Route::post('/mi-delegacion/baja', [MyDelegationController::class, 'noop'])->name('my-delegation.baja');
        Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');
    });

    Route::middleware('permission:Ver partidas')->group(function () {
        Route::get('/partidas', [PartidaController::class, 'index'])->name('partidas.index');
    });
    Route::middleware('permission:Crear partidas')->post('/partidas', [PartidaController::class, 'noop'])->name('partidas.store');
    Route::middleware('permission:Editar partidas')->put('/partidas/{id}', [PartidaController::class, 'noop'])->name('partidas.update');
    Route::middleware('permission:Eliminar partidas')->delete('/partidas/{id}', [PartidaController::class, 'noop'])->name('partidas.destroy');

    Route::middleware('permission:Ver partidas específicas')->group(function () {
        Route::get('/partidas-especificas', [PartidaEspecificaController::class, 'index'])->name('partidas-especificas.index');
    });
    Route::middleware('permission:Exportar partidas específicas')->get('/partidas-especificas/export', [PartidaEspecificaController::class, 'export'])->name('partidas-especificas.export');
    Route::middleware('permission:Crear partidas específicas')->post('/partidas-especificas', [PartidaEspecificaController::class, 'noop'])->name('partidas-especificas.store');
    Route::middleware('permission:Editar partidas específicas')->put('/partidas-especificas/{id}', [PartidaEspecificaController::class, 'noop'])->name('partidas-especificas.update');
    Route::middleware('permission:Eliminar partidas específicas')->delete('/partidas-especificas/{id}', [PartidaEspecificaController::class, 'noop'])->name('partidas-especificas.destroy');

    Route::middleware('permission:Ver productos')->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    });
    Route::middleware('permission:Crear productos')->post('/productos', [ProductoController::class, 'noop'])->name('productos.store');
    Route::middleware('permission:Activar productos')->post('/productos/{producto}/activar', [ProductoController::class, 'noop'])->name('productos.activar');

    Route::middleware('permission:Ver periodos')->group(function () {
        Route::get('/periodos', [PeriodoController::class, 'index'])->name('periodos.index');
    });
    Route::middleware('permission:Crear periodos')->post('/periodos', [PeriodoController::class, 'store'])->name('periodos.store');
    Route::middleware('permission:Editar periodos')->put('/periodos/{periodo}', [PeriodoController::class, 'update'])->name('periodos.update');
    Route::middleware('permission:Eliminar periodos')->delete('/periodos/{periodo}', [PeriodoController::class, 'destroy'])->name('periodos.destroy');
    Route::middleware('permission:Cerrar periodos')->patch('/periodos/{periodo}/cerrar', [PeriodoController::class, 'cerrar'])->name('periodos.cerrar');
    Route::middleware('permission:Reabrir periodos')->patch('/periodos/{periodo}/reabrir', [PeriodoController::class, 'reabrir'])->name('periodos.reabrir');

    Route::middleware('permission:Ver usuarios')->get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::middleware('permission:Crear usuarios')->get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::middleware('permission:Crear usuarios')->post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::middleware('permission:Ver usuarios')->get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::middleware('permission:Editar usuarios')->get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::middleware('permission:Editar usuarios')->put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::middleware('permission:Editar usuarios')->patch('/users/{user}', [AdminUserController::class, 'update']);
    Route::middleware('permission:Eliminar usuarios')->delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::middleware('permission:Ver roles')->get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware('permission:Crear roles')->get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::middleware('permission:Crear roles')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware('permission:Ver roles')->get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::middleware('permission:Editar roles')->get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::middleware('permission:Editar roles')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware('permission:Editar roles')->patch('/roles/{role}', [RoleController::class, 'update']);
    Route::middleware('permission:Eliminar roles')->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::middleware('permission:Ver permisos del sistema')->get('/permissions', [AclPermissionController::class, 'index'])->name('permissions.index');
    Route::middleware('permission:Crear permisos del sistema')->get('/permissions/create', [AclPermissionController::class, 'create'])->name('permissions.create');
    Route::middleware('permission:Crear permisos del sistema')->post('/permissions', [AclPermissionController::class, 'store'])->name('permissions.store');
    Route::middleware('permission:Ver permisos del sistema')->get('/permissions/{permission}', [AclPermissionController::class, 'show'])->name('permissions.show');
    Route::middleware('permission:Editar permisos del sistema')->get('/permissions/{permission}/edit', [AclPermissionController::class, 'edit'])->name('permissions.edit');
    Route::middleware('permission:Editar permisos del sistema')->put('/permissions/{permission}', [AclPermissionController::class, 'update'])->name('permissions.update');
    Route::middleware('permission:Editar permisos del sistema')->patch('/permissions/{permission}', [AclPermissionController::class, 'update']);
    Route::middleware('permission:Eliminar permisos del sistema')->delete('/permissions/{permission}', [AclPermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::middleware('permission:Ver empleados')->group(function () {
        Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
        Route::get('/empleados/lookup', [EmpleadoController::class, 'lookup'])->name('empleados.lookup');
        Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
    });
    Route::middleware('permission:Crear empleados')->post('/empleados', [EmpleadoController::class, 'noop'])->name('empleados.store');
    Route::middleware('permission:Editar empleados')->put('/empleados/{empleado}', [EmpleadoController::class, 'noop'])->name('empleados.update');
    Route::middleware('permission:Eliminar empleados')->delete('/empleados/{empleado}', [EmpleadoController::class, 'noop'])->name('empleados.destroy');

    Route::middleware('permission:Ver delegaciones')->group(function () {
        Route::get('/delegaciones', [DelegacionController::class, 'index'])->name('delegaciones.index');
    });
    Route::middleware('permission:Eliminar delegaciones')->delete('/delegaciones/{id}', [DelegacionController::class, 'noop'])->name('delegaciones.destroy');

    Route::middleware('permission:Ver delegados')->group(function () {
        Route::get('/delegados', [DelegadoController::class, 'index'])->name('delegados.index');
        Route::get('/delegados/{delegado}', [DelegadoController::class, 'show'])->name('delegados.show');
    });
    Route::middleware('permission:Crear delegados')->post('/delegados', [DelegadoController::class, 'noop'])->name('delegados.store');
    Route::middleware('permission:Editar delegados')->group(function () {
        Route::post('/delegados/{delegado}/asociar-usuario', [DelegadoController::class, 'noop'])->name('delegados.asociar-usuario');
        Route::post('/delegados/{delegado}/crear-usuario', [DelegadoController::class, 'noop'])->name('delegados.crear-usuario');
    });
    Route::middleware('permission:Desasociar usuario de delegado')->delete('/delegados/{delegado}/desasociar-usuario', [DelegadoController::class, 'noop'])->name('delegados.desasociar-usuario');

    Route::middleware('permission:Ver dependencias')->group(function () {
        Route::get('/dependencias', [DependenciaController::class, 'index'])->name('dependencias.index');
    });
    Route::middleware('permission:Eliminar dependencias')->delete('/dependencias/{id}', [DependenciaController::class, 'noop'])->name('dependencias.destroy');
});

require __DIR__.'/auth.php';
