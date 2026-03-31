<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DelegadosReporteController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(): View
    {
        $delegados = $this->cx()->table('delegados')
            ->orderBy('nombre_completo')
            ->get();

        $delegacionIdsPorDelegado = $this->cx()->table('delegado_delegacion')
            ->get()
            ->groupBy('delegado_id');

        $delegacionRows = $this->cx()->table('delegaciones')->get()->keyBy('id');

        foreach ($delegados as $d) {
            $ids = collect($delegacionIdsPorDelegado->get($d->id, []))->pluck('delegacion_id')->all();
            $d->delegaciones_claves = collect($ids)
                ->map(fn ($id) => $delegacionRows->get($id)?->clave)
                ->filter()
                ->values()
                ->all();
            $d->empleados_count = $ids === [] ? 0 : $this->cx()->table('empleados')
                ->whereIn('delegacion_id', $ids)
                ->count();
        }

        return view('reportes.delegados.index', [
            'delegados' => $delegados,
            'ejercicio' => (int) config('sivso.ejercicio_actual', 2025),
        ]);
    }

    public function show(int $id): View
    {
        $delegado = $this->cx()->table('delegados')->where('id', $id)->first();
        abort_if($delegado === null, 404);

        $delegacionIds = $this->cx()->table('delegado_delegacion')
            ->where('delegado_id', $id)
            ->pluck('delegacion_id');

        $delegaciones = $this->cx()->table('delegaciones')
            ->whereIn('id', $delegacionIds)
            ->orderBy('clave')
            ->get();

        $empleados = $this->cx()->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->leftJoin('delegaciones as del', 'del.id', '=', 'e.delegacion_id')
            ->whereIn('e.delegacion_id', $delegacionIds)
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->select([
                'e.id',
                'e.nue',
                'e.nombre',
                'e.apellido_paterno',
                'e.apellido_materno',
                'e.ur',
                'd.nombre as dependencia_nombre',
                'del.clave as delegacion_clave',
            ])
            ->get();

        return view('reportes.delegados.show', [
            'delegado' => $delegado,
            'delegaciones' => $delegaciones,
            'empleados' => $empleados,
            'ejercicio' => (int) config('sivso.ejercicio_actual', 2025),
        ]);
    }
}
