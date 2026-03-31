<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DelegacionesController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $q = $this->cx()->table('delegaciones')
            ->orderBy('clave');

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('clave', 'like', $term)
                    ->orWhere('nombre', 'like', $term)
                    ->orWhere('ur', 'like', $term);
            });
        }

        $paginator = $q->paginate(40)->withQueryString();

        $ids = $paginator->getCollection()->pluck('id');

        $empleadosPorDelegacion = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('empleados')
                ->whereIn('delegacion_id', $ids)
                ->groupBy('delegacion_id')
                ->selectRaw('delegacion_id, COUNT(*) as c')
                ->pluck('c', 'delegacion_id');

        $delegadosPorDelegacion = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('delegado_delegacion')
                ->whereIn('delegacion_id', $ids)
                ->groupBy('delegacion_id')
                ->selectRaw('delegacion_id, COUNT(*) as c')
                ->pluck('c', 'delegacion_id');

        $dependenciasPorDelegacion = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('dependencia_delegacion')
                ->whereIn('delegacion_id', $ids)
                ->groupBy('delegacion_id')
                ->selectRaw('delegacion_id, COUNT(*) as c')
                ->pluck('c', 'delegacion_id');

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($row) => [
                'id' => $row->id,
                'clave' => $row->clave,
                'nombre' => $row->nombre,
                'ur' => $row->ur,
                'empleados_count' => (int) ($empleadosPorDelegacion[$row->id] ?? 0),
                'delegados_count' => (int) ($delegadosPorDelegacion[$row->id] ?? 0),
                'dependencias_count' => (int) ($dependenciasPorDelegacion[$row->id] ?? 0),
            ])
        );

        return Inertia::render('Delegaciones/Index', [
            'delegaciones' => $paginator,
            'filters' => ['buscar' => $request->string('buscar')->toString()],
        ]);
    }

    public function destroy(int $id)
    {
        return back();
    }
}
