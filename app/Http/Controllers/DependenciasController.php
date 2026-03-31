<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DependenciasController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $q = $this->cx()->table('dependencias')
            ->orderBy('nombre');

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('nombre', 'like', $term)
                    ->orWhere('codigo', 'like', $term)
                    ->orWhere('ur_texto', 'like', $term);
            });
        }

        $paginator = $q->paginate(40)->withQueryString();

        $ids = $paginator->getCollection()->pluck('id');

        $empleadosPorDependencia = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('empleados')
                ->whereIn('dependencia_id', $ids)
                ->groupBy('dependencia_id')
                ->selectRaw('dependencia_id, COUNT(*) as c')
                ->pluck('c', 'dependencia_id');

        $delegacionesPorDependencia = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('dependencia_delegacion')
                ->whereIn('dependencia_id', $ids)
                ->groupBy('dependencia_id')
                ->selectRaw('dependencia_id, COUNT(*) as c')
                ->pluck('c', 'dependencia_id');

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($row) => [
                'id' => $row->id,
                'nombre' => $row->nombre,
                'codigo' => $row->codigo,
                'ur' => $row->ur,
                'ur_texto' => $row->ur_texto,
                'empleados_count' => (int) ($empleadosPorDependencia[$row->id] ?? 0),
                'delegaciones_count' => (int) ($delegacionesPorDependencia[$row->id] ?? 0),
            ])
        );

        return Inertia::render('Dependencias/Index', [
            'dependencias' => $paginator,
            'filters' => ['buscar' => $request->string('buscar')->toString()],
        ]);
    }

    public function destroy(int $id)
    {
        return back();
    }
}
