<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MyDelegationController;
use App\Http\Controllers\PeriodosController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/mi-delegacion', [MyDelegationController::class, 'index'])->name('my-delegation.index');
    Route::get('/mi-delegacion/{id}', [MyDelegationController::class, 'show'])->name('my-delegation.show');

    Route::get('/periodos', [PeriodosController::class, 'index'])->name('periodos.index');
    Route::post('/periodos', [PeriodosController::class, 'store'])->name('periodos.store');
    Route::put('/periodos/{periodo}', [PeriodosController::class, 'update'])->name('periodos.update');
    Route::delete('/periodos/{periodo}', [PeriodosController::class, 'destroy'])->name('periodos.destroy');
    Route::patch('/periodos/{periodo}/cerrar', [PeriodosController::class, 'cerrar'])->name('periodos.cerrar');
    Route::patch('/periodos/{periodo}/reabrir', [PeriodosController::class, 'reabrir'])->name('periodos.reabrir');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Vistas agregadas (Mocks)
    Route::get('/empleados', function () { return Inertia::render('Empleados/Index'); })->name('empleados.index');
    Route::delete('/empleados/{id}', function () { return back(); })->name('empleados.destroy');
    
    Route::get('/delegaciones', function () { return Inertia::render('Delegaciones/Index'); })->name('delegaciones.index');
    Route::delete('/delegaciones/{id}', function () { return back(); })->name('delegaciones.destroy');
    
    Route::get('/delegados', function () { return Inertia::render('Delegados/Index'); })->name('delegados.index');
    Route::delete('/delegados/{id}', function () { return back(); })->name('delegados.destroy');
    
    Route::get('/dependencias', function () { return Inertia::render('Dependencias/Index'); })->name('dependencias.index');
    Route::delete('/dependencias/{id}', function () { return back(); })->name('dependencias.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
