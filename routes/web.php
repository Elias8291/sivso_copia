<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CatalogoDelegadosController;
use App\Http\Controllers\CatalogoEmpleadosController;
use App\Http\Controllers\DelegacionesController;
use App\Http\Controllers\DelegadosReporteController;
use App\Http\Controllers\DependenciasController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\EmpleadosReporteController;
use App\Http\Controllers\LineasPresupuestalesProvisionalController;
use App\Http\Controllers\MyDelegationController;
use App\Http\Controllers\PartidasController;
use App\Http\Controllers\PartidasEspecificasController;
use App\Http\Controllers\PeriodosController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest');

/** Provisional: sin auth — retirar o proteger en producción */
Route::get('/provisional/lineas-presupuestales', [LineasPresupuestalesProvisionalController::class, 'index'])
    ->name('provisional.lineas-presupuestales');
Route::post('/provisional/lineas-presupuestales/{ppe}', [LineasPresupuestalesProvisionalController::class, 'update'])
    ->name('provisional.lineas-presupuestales.update');

Route::get('/dashboard', function () {
    $cx = DB::connection('copiasivso');
    $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

    return Inertia::render('Dashboard', [
        'stats' => [
            'empleados' => $cx->table('empleados')->count(),
            'delegaciones' => $cx->table('delegaciones')->count(),
            'dependencias' => $cx->table('dependencias')->count(),
            'delegados' => $cx->table('delegados')->count(),
            'productos' => $cx->table('productos')
                ->join('producto_precios', function ($j) use ($ejercicio) {
                    $j->on('producto_precios.producto_id', '=', 'productos.id')
                        ->where('producto_precios.anio', '=', $ejercicio);
                })->count(),
            'solicitudes' => $cx->table('solicitudes_vestuario')
                ->where('anio', $ejercicio)->count(),
            'importe_total' => (float) $cx->table('solicitudes_vestuario')
                ->where('anio', $ejercicio)
                ->sum('importe_total'),
        ],
        'ejercicio' => $ejercicio,
    ]);
})->middleware(['auth', 'verified', 'password.changed'])->name('dashboard');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:delegation.self')->group(function () {
        Route::get('/mi-delegacion', [MyDelegationController::class, 'index'])->name('my-delegation.index');
        Route::post('/mi-delegacion/tallas', [MyDelegationController::class, 'saveTallas'])->name('my-delegation.save-tallas');
        Route::post('/mi-delegacion/baja', [MyDelegationController::class, 'baja'])->name('my-delegation.baja');
        Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');
    });

    Route::middleware('permission:reportes.delegados')->group(function () {
        Route::get('/reportes/delegados', [DelegadosReporteController::class, 'index'])->name('reportes.delegados.index');
        Route::get('/reportes/delegados/{id}', [DelegadosReporteController::class, 'show'])->name('reportes.delegados.show');
    });

    Route::get('/reportes/empleados/{empleado}/productos', [EmpleadosReporteController::class, 'productos'])
        ->middleware('permission:reportes.empleados-productos')
        ->name('reportes.empleados.productos');

    Route::get('/partidas', [PartidasController::class, 'index'])
        ->middleware('permission:partidas.view')
        ->name('partidas.index');
    Route::post('/partidas', [PartidasController::class, 'store'])
        ->middleware('permission:partidas.create')
        ->name('partidas.store');
    Route::put('/partidas/{id}', [PartidasController::class, 'update'])
        ->middleware('permission:partidas.update')
        ->name('partidas.update');
    Route::delete('/partidas/{id}', [PartidasController::class, 'destroy'])
        ->middleware('permission:partidas.delete')
        ->name('partidas.destroy');

    Route::get('/partidas-especificas', [PartidasEspecificasController::class, 'index'])
        ->middleware('permission:partidas-especificas.view')
        ->name('partidas-especificas.index');
    Route::get('/partidas-especificas/export', [PartidasEspecificasController::class, 'exportCsv'])
        ->middleware('permission:partidas-especificas.export')
        ->name('partidas-especificas.export');
    Route::post('/partidas-especificas', [PartidasEspecificasController::class, 'store'])
        ->middleware('permission:partidas-especificas.create')
        ->name('partidas-especificas.store');
    Route::put('/partidas-especificas/{id}', [PartidasEspecificasController::class, 'update'])
        ->middleware('permission:partidas-especificas.update')
        ->name('partidas-especificas.update');
    Route::delete('/partidas-especificas/{id}', [PartidasEspecificasController::class, 'destroy'])
        ->middleware('permission:partidas-especificas.delete')
        ->name('partidas-especificas.destroy');

    Route::get('/productos', [ProductosController::class, 'index'])
        ->middleware('permission:productos.view')
        ->name('productos.index');
    Route::post('/productos', [ProductosController::class, 'store'])
        ->middleware('permission:productos.create')
        ->name('productos.store');
    Route::post('/productos/{producto}/activar', [ProductosController::class, 'activar'])
        ->middleware('permission:productos.activate')
        ->name('productos.activar');

    Route::get('/periodos', [PeriodosController::class, 'index'])
        ->middleware('permission:periodos.view')
        ->name('periodos.index');
    Route::post('/periodos', [PeriodosController::class, 'store'])
        ->middleware('permission:periodos.create')
        ->name('periodos.store');
    Route::put('/periodos/{periodo}', [PeriodosController::class, 'update'])
        ->middleware('permission:periodos.update')
        ->name('periodos.update');
    Route::delete('/periodos/{periodo}', [PeriodosController::class, 'destroy'])
        ->middleware('permission:periodos.delete')
        ->name('periodos.destroy');
    Route::patch('/periodos/{periodo}/cerrar', [PeriodosController::class, 'cerrar'])
        ->middleware('permission:periodos.cerrar-reabrir')
        ->name('periodos.cerrar');
    Route::patch('/periodos/{periodo}/reabrir', [PeriodosController::class, 'reabrir'])
        ->middleware('permission:periodos.cerrar-reabrir')
        ->name('periodos.reabrir');

    Route::resource('users', UserController::class)->middleware([
        'index' => 'permission:users.view',
        'show' => 'permission:users.view',
        'create' => 'permission:users.create',
        'store' => 'permission:users.create',
        'edit' => 'permission:users.update',
        'update' => 'permission:users.update',
        'destroy' => 'permission:users.delete',
    ]);
    Route::resource('roles', RoleController::class)->middleware([
        'index' => 'permission:roles.view',
        'show' => 'permission:roles.view',
        'create' => 'permission:roles.create',
        'store' => 'permission:roles.create',
        'edit' => 'permission:roles.update',
        'update' => 'permission:roles.update',
        'destroy' => 'permission:roles.delete',
    ]);
    Route::resource('permissions', PermissionController::class)->middleware([
        'index' => 'permission:permissions.view',
        'show' => 'permission:permissions.view',
        'create' => 'permission:permissions.create',
        'store' => 'permission:permissions.create',
        'edit' => 'permission:permissions.update',
        'update' => 'permission:permissions.update',
        'destroy' => 'permission:permissions.delete',
    ]);

    Route::get('/empleados', [CatalogoEmpleadosController::class, 'index'])
        ->middleware('permission:empleados.view')
        ->name('empleados.index');
    Route::get('/empleados/lookup', function (Request $request) {
        $ur = $request->integer('ur');
        if (! $ur) {
            return response()->json([]);
        }

        return response()->json(
            DB::connection('copiasivso')
                ->table('empleados')
                ->where('ur', $ur)
                ->whereNotNull('nue')
                ->orderBy('apellido_paterno')->orderBy('nombre')
                ->select(['nue', 'nombre', 'apellido_paterno', 'apellido_materno'])
                ->limit(60)
                ->get()
                ->map(fn ($e) => [
                    'nue' => $e->nue,
                    'nombre' => trim("{$e->nombre} {$e->apellido_paterno} {$e->apellido_materno}"),
                ])
        );
    })->middleware('permission:empleados.view')->name('empleados.lookup');
    Route::post('/empleados', [EmpleadosController::class, 'store'])
        ->middleware('permission:empleados.create')
        ->name('empleados.store');
    Route::put('/empleados/{empleado}', [EmpleadosController::class, 'update'])
        ->middleware('permission:empleados.update')
        ->name('empleados.update');
    Route::delete('/empleados/{empleado}', [EmpleadosController::class, 'destroy'])
        ->middleware('permission:empleados.delete')
        ->name('empleados.destroy');
    Route::get('/empleados/{empleado}', [CatalogoEmpleadosController::class, 'show'])
        ->middleware('permission:empleados.view')
        ->name('empleados.show');

    Route::get('/delegaciones', [DelegacionesController::class, 'index'])
        ->middleware('permission:delegaciones.view')
        ->name('delegaciones.index');
    Route::delete('/delegaciones/{id}', [DelegacionesController::class, 'destroy'])
        ->middleware('permission:delegaciones.delete')
        ->name('delegaciones.destroy');

    Route::get('/delegados', [CatalogoDelegadosController::class, 'index'])
        ->middleware('permission:delegados.view')
        ->name('delegados.index');
    Route::post('/delegados', [CatalogoDelegadosController::class, 'store'])
        ->middleware('permission:delegados.create')
        ->name('delegados.store');
    Route::get('/delegados/{delegado}', [CatalogoDelegadosController::class, 'show'])
        ->middleware('permission:delegados.view')
        ->name('delegados.show');
    Route::post('/delegados/{delegado}/asociar-usuario', [CatalogoDelegadosController::class, 'asociarUsuario'])
        ->middleware('permission:delegados.manage-users')
        ->name('delegados.asociar-usuario');
    Route::post('/delegados/{delegado}/crear-usuario', [CatalogoDelegadosController::class, 'crearUsuario'])
        ->middleware('permission:delegados.manage-users')
        ->name('delegados.crear-usuario');
    Route::delete('/delegados/{delegado}/desasociar-usuario', [CatalogoDelegadosController::class, 'desasociarUsuario'])
        ->middleware('permission:delegados.manage-users')
        ->name('delegados.desasociar-usuario');

    Route::get('/dependencias', [DependenciasController::class, 'index'])
        ->middleware('permission:dependencias.view')
        ->name('dependencias.index');
    Route::delete('/dependencias/{id}', [DependenciasController::class, 'destroy'])
        ->middleware('permission:dependencias.delete')
        ->name('dependencias.destroy');
});

require __DIR__.'/auth.php';
