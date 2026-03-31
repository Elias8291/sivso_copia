<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogoEmpleadosController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    /**
     * Años elegibles en UI: 2025, año calendario actual y ejercicio configurado (SIVSO_EJERCICIO).
     *
     * @return list<int>
     */
    private function aniosBaseParaListado(): array
    {
        $cfg = (int) config('sivso.ejercicio_actual', 2025);

        return collect([2025, (int) date('Y'), $cfg])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function index(Request $request): Response
    {
        $ejercicioCfg = (int) config('sivso.ejercicio_actual', 2025);
        $aniosDisponibles = $this->aniosBaseParaListado();
        $anioLista = (int) $request->query('anio', $ejercicioCfg);
        if (! in_array($anioLista, $aniosDisponibles, true)) {
            $anioLista = $ejercicioCfg;
        }

        $q = $this->cx()->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->leftJoin('delegaciones as del', 'del.id', '=', 'e.delegacion_id')
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
                'e.dependencia_id',
                'e.delegacion_id',
                'd.nombre as dependencia_nombre',
                'del.clave as delegacion_clave',
            ]);

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('e.nombre', 'like', $term)
                    ->orWhere('e.apellido_paterno', 'like', $term)
                    ->orWhere('e.apellido_materno', 'like', $term)
                    ->orWhere('e.nue', 'like', $term)
                    ->orWhere('d.nombre', 'like', $term)
                    ->orWhere('del.clave', 'like', $term);
            });
        }

        $paginator = $q->paginate(40)->withQueryString();
        $ids = $paginator->getCollection()->pluck('id');
        $productosPorEmpleado = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('solicitudes_vestuario')
                ->whereIn('empleado_id', $ids)
                ->where('anio', $anioLista)
                ->groupBy('empleado_id')
                ->selectRaw('empleado_id, COUNT(*) as c')
                ->pluck('c', 'empleado_id');

        $paginator->setCollection(
            $paginator->getCollection()->map(function ($row) use ($productosPorEmpleado) {
                $nombreCompleto = trim(implode(' ', array_filter([
                    $row->nombre,
                    $row->apellido_paterno,
                    $row->apellido_materno,
                ])));

                $puesto = trim(collect([
                    $row->dependencia_nombre,
                    $row->delegacion_clave ? 'Del. '.$row->delegacion_clave : null,
                ])->filter()->implode(' · '));

                return [
                    'id' => $row->id,
                    'nombre' => $row->nombre,
                    'apellido_paterno' => $row->apellido_paterno,
                    'apellido_materno' => $row->apellido_materno,
                    'nombre_listado' => $nombreCompleto !== '' ? $nombreCompleto : '—',
                    'nue' => $row->nue,
                    'rfc' => $row->nue,
                    'ur' => $row->ur,
                    'dependencia_id' => $row->dependencia_id,
                    'delegacion_id' => $row->delegacion_id,
                    'dependencia' => $row->dependencia_nombre,
                    'delegacion' => $row->delegacion_clave,
                    'puesto' => $puesto !== '' ? $puesto : '—',
                    'activo' => true,
                    'productos_count' => (int) ($productosPorEmpleado[$row->id] ?? 0),
                ];
            })
        );

        $delegaciones = $this->cx()->table('delegaciones')
            ->orderBy('clave')
            ->get(['id', 'nombre', 'clave'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'nombre' => $d->clave.($d->nombre ? ' — '.$d->nombre : ''),
            ]);

        $dependencias = $this->cx()->table('dependencias')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn ($d) => ['id' => $d->id, 'nombre' => $d->nombre]);

        return Inertia::render('Empleados/Index', [
            'empleados' => $paginator,
            'delegaciones' => $delegaciones,
            'dependencias' => $dependencias,
            'ejercicio' => $anioLista,
            'anios_disponibles' => $aniosDisponibles,
            'ejercicio_config' => $ejercicioCfg,
            'filters' => [
                'buscar' => $request->string('buscar')->toString(),
                'anio' => $anioLista,
            ],
        ]);
    }

    public function show(Request $request, int $empleado): Response
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
        $aniosDesdeDb = $this->cx()->table('solicitudes_vestuario')
            ->where('empleado_id', $empleado)
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(fn ($y) => (int) $y);

        $aniosOpciones = collect([2025, (int) date('Y'), $ejercicioDefault])
            ->merge($aniosDesdeDb)
            ->unique()
            ->sortDesc()
            ->values();

        $anioFiltro = (int) $request->query('anio', $ejercicioDefault);
        if (! $aniosOpciones->contains(fn ($y) => (int) $y === $anioFiltro)) {
            $anioFiltro = $ejercicioDefault;
        }

        $q = $this->cx()->table('solicitudes_vestuario as s')
            ->leftJoin('productos as p', 'p.id', '=', 's.producto_id')
            ->leftJoin('partidas as pa', 'pa.id', '=', 'p.partida_id')
            ->leftJoin('tipos_partida_especifica as tpe', 'tpe.id', '=', 'p.tipo_partida_especifica_id')
            ->leftJoin('partidas_especificas as pe', 'pe.id', '=', 's.partida_especifica_id')
            ->where('s.empleado_id', $empleado)
            ->groupBy('s.producto_id', 's.anio')
            ->orderByDesc('s.anio')
            ->orderBy('pa.no_partida')
            ->orderBy('p.descripcion')
            ->selectRaw(
                'MIN(s.id) as solicitud_id, '.
                's.anio, '.
                's.producto_id, '.
                'MAX(s.talla) as talla, '.
                'SUM(s.cantidad) as cantidad, '.
                'MAX(s.precio_unitario) as precio_unitario, '.
                'SUM(s.importe) as importe, '.
                'SUM(s.iva) as iva, '.
                'SUM(s.importe_total) as importe_total, '.
                'MAX(s.estado) as estado, '.
                'MAX(s.es_sustitucion) as es_sustitucion, '.
                'MAX(s.no_partida_snapshot) as no_partida_snapshot, '.
                'MAX(s.clave_partida_snapshot) as clave_partida_snapshot, '.
                'MAX(s.clave_para_ejercicio_snapshot) as clave_para_ejercicio_snapshot, '.
                'MAX(p.descripcion) as producto_descripcion, '.
                'MAX(p.codigo) as producto_codigo, '.
                'MAX(p.unidad_medida) as unidad_medida, '.
                'MAX(p.marca) as marca, '.
                'MAX(p.medida) as medida, '.
                'MAX(pa.no_partida) as no_partida, '.
                'MAX(tpe.codigo) as partida_especifica_codigo, '.
                'MAX(pe.clave) as partida_especifica_clave, '.
                'MAX(pe.descripcion) as partida_especifica_linea, '.
                'COUNT(*) as registros'
            );

        if ($soloEjercicio) {
            $q->where('s.anio', $anioFiltro);
        }

        $lineas = $q->get();

        $nombreCompleto = trim(implode(' ', array_filter([
            $empleadoRow->nombre,
            $empleadoRow->apellido_paterno,
            $empleadoRow->apellido_materno,
        ])));

        return Inertia::render('Empleados/Show', [
            'empleado' => [
                'id' => $empleadoRow->id,
                'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : '—',
                'nue' => $empleadoRow->nue,
                'ur' => $empleadoRow->ur,
                'dependencia_nombre' => $empleadoRow->dependencia_nombre,
                'delegacion_clave' => $empleadoRow->delegacion_clave,
            ],
            'lineas' => $lineas,
            'ejercicio' => $ejercicioDefault,
            'anioFiltro' => $anioFiltro,
            'soloEjercicio' => $soloEjercicio,
            'anios' => $aniosOpciones,
            'delegadoId' => $request->query('delegado'),
        ]);
    }
}
