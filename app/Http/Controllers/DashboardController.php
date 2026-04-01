<?php

namespace App\Http\Controllers;

use App\Models\AsignacionEmpleadoProducto;
use App\Models\Delegacion;
use App\Models\Delegado;
use App\Models\Dependencia;
use App\Models\Empleado;
use App\Models\ProductoCotizado;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2026);

        return Inertia::render('Dashboard', [
            'stats' => [
                'empleados' => Empleado::query()->count(),
                'delegaciones' => Delegacion::query()->count(),
                'dependencias' => Dependencia::query()->count(),
                'delegados' => Delegado::query()->count(),
                'productos' => ProductoCotizado::query()->where('anio', $ejercicio)->count(),
                'solicitudes' => AsignacionEmpleadoProducto::query()->where('anio', $ejercicio)->count(),
                'importe_total' => (float) ProductoCotizado::query()->where('anio', $ejercicio)->sum('total'),
            ],
            'ejercicio' => $ejercicio,
        ]);
    }
}
