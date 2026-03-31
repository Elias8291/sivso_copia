<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmpleadosReporteController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function productos(Request $request, int $empleado): View
    {
        $empleadoRow = $this->cx()->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->leftJoin('delegaciones as del', 'del.id', '=', 'e.delegacion_id')
            ->where('e.id', $empleado)
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
            ->first();

        abort_if($empleadoRow === null, 404);

        $ejercicioDefault = (int) config('sivso.ejercicio_actual', 2025);
        $soloEjercicio = ! $request->boolean('todos');
        $anioFiltro = (int) $request->query('anio', $ejercicioDefault);

        $q = $this->cx()->table('solicitudes_vestuario as s')
            ->join('productos as p', 'p.id', '=', 's.producto_id')
            ->leftJoin('partidas as pa', 'pa.id', '=', 'p.partida_id')
            ->leftJoin('tipos_partida_especifica as tpe', 'tpe.id', '=', 'p.tipo_partida_especifica_id')
            ->leftJoin('partidas_especificas as pe', 'pe.id', '=', 's.partida_especifica_id')
            ->where('s.empleado_id', $empleado)
            ->orderByDesc('s.anio')
            ->orderBy('s.id')
            ->select([
                's.id as solicitud_id',
                's.anio',
                's.talla',
                's.cantidad',
                's.precio_unitario',
                's.importe',
                's.iva',
                's.importe_total',
                's.no_partida_snapshot',
                's.clave_partida_snapshot',
                's.clave_para_ejercicio_snapshot',
                'p.descripcion as producto_descripcion',
                'p.codigo as producto_codigo',
                'p.unidad_medida',
                'p.marca',
                'p.medida',
                'pa.no_partida',
                'tpe.codigo as partida_especifica_codigo',
                'pe.clave as partida_especifica_clave',
                'pe.descripcion as partida_especifica_linea',
            ]);

        if ($soloEjercicio) {
            $q->where('s.anio', $anioFiltro);
        }

        $lineas = $q->get();

        return view('reportes.empleados.productos', [
            'empleado' => $empleadoRow,
            'lineas' => $lineas,
            'ejercicio' => $ejercicioDefault,
            'anioFiltro' => $anioFiltro,
            'soloEjercicio' => $soloEjercicio,
            'delegadoId' => $request->query('delegado'),
        ]);
    }
}
