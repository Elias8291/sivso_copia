<?php

namespace App\Http\Controllers;

use App\Models\Delegacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DelegacionController extends Controller
{
    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $empleados = (string) $request->input('empleados', 'Todos');
        $delegados = (string) $request->input('delegados', 'Todos');

        $query = Delegacion::query()
            ->with('dependenciaReferencia')
            ->withCount(['dependencias', 'empleados', 'delegados']);

        if ($buscar !== '') {
            $like = '%'.$buscar.'%';
            $query->where(function ($q) use ($like, $buscar) {
                $q->where('codigo', 'like', $like);
                if (is_numeric($buscar)) {
                    $q->orWhere('ur_referencia', (int) $buscar);
                }
            });
        }

        if ($empleados === 'ConEmpleados') {
            $query->has('empleados');
        } elseif ($empleados === 'SinEmpleados') {
            $query->doesntHave('empleados');
        }

        if ($delegados === 'ConDelegados') {
            $query->has('delegados');
        } elseif ($delegados === 'SinDelegados') {
            $query->doesntHave('delegados');
        }

        $delegaciones = $query
            ->orderBy('codigo')
            ->paginate(20)
            ->withQueryString()
            ->through(function (Delegacion $d) {
                $ref = $d->dependenciaReferencia;

                return [
                    'id' => $d->codigo,
                    'clave' => $d->codigo,
                    'codigo' => $d->codigo,
                    'ur' => $d->ur_referencia,
                    'nombre' => $ref?->nombre,
                    'dependencias_count' => (int) $d->dependencias_count,
                    'empleados_count' => (int) $d->empleados_count,
                    'delegados_count' => (int) $d->delegados_count,
                ];
            });

        return Inertia::render('Delegaciones/Index', [
            'delegaciones' => $delegaciones,
            'filters' => [
                'buscar' => $buscar,
                'empleados' => $empleados,
                'delegados' => $delegados,
            ],
        ]);
    }
}
