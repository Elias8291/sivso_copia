<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DependenciaController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $empleados = (string) $request->input('empleados', 'Todos');

        $query = Dependencia::query()
            ->withCount(['delegaciones', 'empleados']);

        if ($buscar !== '') {
            $like = '%'.$buscar.'%';
            $query->where(function ($q) use ($like, $buscar) {
                $q->where('nombre', 'like', $like)
                    ->orWhere('nombre_corto', 'like', $like);
                if (is_numeric($buscar)) {
                    $q->orWhere('ur', (int) $buscar);
                }
            });
        }

        if ($empleados === 'ConEmpleados') {
            $query->has('empleados');
        } elseif ($empleados === 'SinEmpleados') {
            $query->doesntHave('empleados');
        }

        $dependencias = $query
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Dependencia $d) => [
                'id' => $d->ur,
                'ur' => $d->ur,
                'nombre' => $d->nombre,
                'nombre_corto' => $d->nombre_corto,
                'delegaciones_count' => (int) $d->delegaciones_count,
                'empleados_count' => (int) $d->empleados_count,
            ]);

        return Inertia::render('Dependencias/Index', [
            'dependencias' => $dependencias,
            'filters' => [
                'buscar' => $buscar,
                'empleados' => $empleados,
            ],
        ]);
    }
}
