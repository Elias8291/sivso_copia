<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CatalogoDelegadosController;
use App\Http\Controllers\CatalogoEmpleadosController;
use App\Http\Controllers\DelegacionesController;
use App\Http\Controllers\DelegadosReporteController;
use App\Http\Controllers\DependenciasController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\EmpleadosReporteController;
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
    Route::get('/mi-delegacion', [MyDelegationController::class, 'index'])->name('my-delegation.index');
    Route::post('/mi-delegacion/tallas', [MyDelegationController::class, 'saveTallas'])->name('my-delegation.save-tallas');
    Route::post('/mi-delegacion/baja', [MyDelegationController::class, 'baja'])->name('my-delegation.baja');
    Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');

    Route::get('/reportes/delegados', [DelegadosReporteController::class, 'index'])->name('reportes.delegados.index');
    Route::get('/reportes/delegados/{id}', [DelegadosReporteController::class, 'show'])->name('reportes.delegados.show');
    Route::get('/reportes/empleados/{empleado}/productos', [EmpleadosReporteController::class, 'productos'])->name('reportes.empleados.productos');

    Route::get('/partidas', [PartidasController::class, 'index'])->name('partidas.index');
    Route::post('/partidas', [PartidasController::class, 'store'])->name('partidas.store');
    Route::put('/partidas/{id}', [PartidasController::class, 'update'])->name('partidas.update');
    Route::delete('/partidas/{id}', [PartidasController::class, 'destroy'])->name('partidas.destroy');

    Route::get('/partidas-especificas', [PartidasEspecificasController::class, 'index'])->name('partidas-especificas.index');
    Route::post('/partidas-especificas', [PartidasEspecificasController::class, 'store'])->name('partidas-especificas.store');
    Route::put('/partidas-especificas/{id}', [PartidasEspecificasController::class, 'update'])->name('partidas-especificas.update');
    Route::delete('/partidas-especificas/{id}', [PartidasEspecificasController::class, 'destroy'])->name('partidas-especificas.destroy');

    Route::get('/productos', [ProductosController::class, 'index'])->name('productos.index');
    Route::post('/productos', [ProductosController::class, 'store'])->name('productos.store');
    Route::post('/productos/{producto}/activar', [ProductosController::class, 'activar'])->name('productos.activar');

    Route::get('/periodos', [PeriodosController::class, 'index'])->name('periodos.index');
    Route::post('/periodos', [PeriodosController::class, 'store'])->name('periodos.store');
    Route::put('/periodos/{periodo}', [PeriodosController::class, 'update'])->name('periodos.update');
    Route::delete('/periodos/{periodo}', [PeriodosController::class, 'destroy'])->name('periodos.destroy');
    Route::patch('/periodos/{periodo}/cerrar', [PeriodosController::class, 'cerrar'])->name('periodos.cerrar');
    Route::patch('/periodos/{periodo}/reabrir', [PeriodosController::class, 'reabrir'])->name('periodos.reabrir');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    Route::get('/empleados', [CatalogoEmpleadosController::class, 'index'])->name('empleados.index');
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
    })->name('empleados.lookup');
    Route::post('/empleados', [EmpleadosController::class, 'store'])->name('empleados.store');
    Route::put('/empleados/{empleado}', [EmpleadosController::class, 'update'])->name('empleados.update');
    Route::delete('/empleados/{empleado}', [EmpleadosController::class, 'destroy'])->name('empleados.destroy');
    Route::get('/empleados/{empleado}', [CatalogoEmpleadosController::class, 'show'])->name('empleados.show');

    Route::get('/delegaciones', [DelegacionesController::class, 'index'])->name('delegaciones.index');
    Route::delete('/delegaciones/{id}', [DelegacionesController::class, 'destroy'])->name('delegaciones.destroy');

    Route::get('/delegados', [CatalogoDelegadosController::class, 'index'])->name('delegados.index');
    Route::post('/delegados', [CatalogoDelegadosController::class, 'store'])->name('delegados.store');
    Route::get('/delegados/{delegado}', [CatalogoDelegadosController::class, 'show'])->name('delegados.show');
    Route::post('/delegados/{delegado}/asociar-usuario', [CatalogoDelegadosController::class, 'asociarUsuario'])->name('delegados.asociar-usuario');
    Route::post('/delegados/{delegado}/crear-usuario', [CatalogoDelegadosController::class, 'crearUsuario'])->name('delegados.crear-usuario');
    Route::delete('/delegados/{delegado}/desasociar-usuario', [CatalogoDelegadosController::class, 'desasociarUsuario'])->name('delegados.desasociar-usuario');

    Route::get('/dependencias', [DependenciasController::class, 'index'])->name('dependencias.index');
    Route::delete('/dependencias/{id}', [DependenciasController::class, 'destroy'])->name('dependencias.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
