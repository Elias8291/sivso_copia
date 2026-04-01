<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use App\Models\AsignacionEmpleadoProducto;
use App\Models\Delegacion;
use App\Models\Dependencia;
use App\Models\Empleado;
use App\Models\ProductoCotizado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class EmpleadoController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $ej = $this->ejercicio($request);
        $defEj = (int) config('sivso.ejercicio_actual', 2026);

        $buscar = trim((string) $request->input('buscar', ''));
        $ur = $request->filled('ur') ? (int) $request->input('ur') : null;
        $delegacionCodigo = $request->filled('delegacion') ? (string) $request->input('delegacion') : null;

        $query = Empleado::query()
            ->with(['dependencia', 'delegacion'])
            ->withCount([
                'asignaciones as productos_count' => fn ($q) => $q->where('anio', $ej),
            ])
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno');

        if ($buscar !== '') {
            $like = '%'.$buscar.'%';
            $query->where(function ($q) use ($like) {
                $q->where('nombre', 'like', $like)
                    ->orWhere('apellido_paterno', 'like', $like)
                    ->orWhere('apellido_materno', 'like', $like)
                    ->orWhere('nue', 'like', $like)
                    ->orWhere('delegacion_codigo', 'like', $like)
                    ->orWhereHas('dependencia', fn ($d) => $d->where('nombre', 'like', $like));
            });
        }

        if ($ur !== null) {
            $query->where('ur', $ur);
        }

        if ($delegacionCodigo !== null && $delegacionCodigo !== '') {
            $query->where('delegacion_codigo', $delegacionCodigo);
        }

        $empleados = $query
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Empleado $e) => $this->empleadoIndexRow($e));

        $aniosDisponibles = array_values(array_unique(array_merge(
            AsignacionEmpleadoProducto::query()->distinct()->orderByDesc('anio')->pluck('anio')->all(),
            [$defEj, $ej]
        )));
        rsort($aniosDisponibles, SORT_NUMERIC);

        $dependencias = Dependencia::query()
            ->orderBy('nombre')
            ->get(['ur', 'nombre'])
            ->map(fn (Dependencia $d) => [
                'id' => $d->ur,
                'nombre' => $d->nombre,
            ])
            ->values()
            ->all();

        $delegaciones = Delegacion::query()
            ->orderBy('codigo')
            ->get(['codigo'])
            ->map(fn (Delegacion $d) => [
                'id' => $d->codigo,
                'nombre' => $d->codigo,
            ])
            ->values()
            ->all();

        return Inertia::render('Empleados/Index', [
            'empleados' => $empleados,
            'ejercicio' => $ej,
            'anios_disponibles' => $aniosDisponibles,
            'filters' => [
                'buscar' => $buscar,
                'anio' => $ej,
                'ur' => $ur,
                'delegacion' => $delegacionCodigo,
            ],
            'delegaciones' => $delegaciones,
            'dependencias' => $dependencias,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function empleadoIndexRow(Empleado $e): array
    {
        $nombreListado = trim(implode(' ', array_filter([
            $e->nombre,
            $e->apellido_paterno,
            $e->apellido_materno,
        ])));

        return [
            'id' => $e->id,
            'nombre' => $e->nombre,
            'apellido_paterno' => $e->apellido_paterno,
            'apellido_materno' => $e->apellido_materno,
            'nombre_listado' => $nombreListado !== '' ? $nombreListado : '—',
            'nue' => $e->nue,
            'puesto' => null,
            'dependencia' => $e->dependencia?->nombre ?? '—',
            'delegacion' => $e->delegacion_codigo,
            'dependencia_id' => $e->ur,
            'delegacion_id' => $e->delegacion_codigo,
            'productos_count' => (int) ($e->productos_count ?? 0),
        ];
    }

    public function lookup(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function show(Request $request, string $empleado): Response
    {
        $empleadoModel = Empleado::query()
            ->with(['dependencia', 'delegacion'])
            ->findOrFail((int) $empleado);

        $defEj = (int) config('sivso.ejercicio_actual', 2026);
        $soloEjercicio = ! $request->boolean('todos');
        $anioFiltro = $this->ejercicio($request);

        $anios = AsignacionEmpleadoProducto::query()
            ->where('empleado_id', $empleadoModel->id)
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->values()
            ->all();
        $anios = array_values(array_unique(array_merge($anios, [$defEj, $anioFiltro])));
        rsort($anios, SORT_NUMERIC);

        $asignacionesQuery = AsignacionEmpleadoProducto::query()
            ->where('empleado_id', $empleadoModel->id)
            ->with([
                'productoLicitado.clasificacionPrincipal',
                'productoCotizado.clasificacionPrincipal',
            ]);

        if ($soloEjercicio) {
            $asignacionesQuery->where('anio', $anioFiltro);
        }

        $asignaciones = $asignacionesQuery
            ->orderBy('anio')
            ->orderBy('id')
            ->get();

        /** @var list<array<string, mixed>> $lineasCotizado asignaciones con producto_cotizado_id → producto e importe desde producto_cotizado */
        $lineasCotizado = [];
        /** @var list<array<string, mixed>> $lineasLicitacion todas las asignaciones del filtro → vista solo con producto_licitado (catálogo licitación) */
        $lineasLicitacion = [];

        /** @var array<string, int> */
        $aggEntregaClasificacion = [];
        /** @var array<string, int> */
        $aggMuestraClasificacion = [];

        foreach ($asignaciones as $a) {
            $piezas = $this->piezasConteoAsignacion($a);

            $nomMuestra = $this->nombreClasificacionDesdeLicitacion($a);
            $aggMuestraClasificacion[$nomMuestra] = ($aggMuestraClasificacion[$nomMuestra] ?? 0) + $piezas;

            $lineasLicitacion[] = $this->asignacionToLineaSoloLicitacion($a);

            $cotizado = $a->productoCotizado;
            $enlazaCotizado = $a->producto_cotizado_id !== null && $cotizado !== null;

            if ($enlazaCotizado) {
                $nomEntrega = $this->nombreClasificacionDesdeCotizado($a, $cotizado);
                $aggEntregaClasificacion[$nomEntrega] = ($aggEntregaClasificacion[$nomEntrega] ?? 0) + $piezas;

                $lineasCotizado[] = $this->asignacionToLinea($a, $cotizado);
            }
        }

        $nombreCompleto = trim(implode(' ', array_filter([
            $empleadoModel->nombre,
            $empleadoModel->apellido_paterno,
            $empleadoModel->apellido_materno,
        ])));

        return Inertia::render('Empleados/Show', [
            'empleado' => [
                'id' => $empleadoModel->id,
                'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : '—',
                'nue' => $empleadoModel->nue,
                'ur' => $empleadoModel->ur,
                'dependencia_nombre' => $empleadoModel->dependencia?->nombre,
                'delegacion_clave' => $empleadoModel->delegacion_codigo,
            ],
            'lineas_cotizado' => $lineasCotizado,
            'lineas_licitacion' => $lineasLicitacion,
            'resumen_entrega_clasificacion' => $this->resumenClasificacionOrdenado($aggEntregaClasificacion),
            'resumen_muestra_clasificacion' => $this->resumenClasificacionOrdenado($aggMuestraClasificacion),
            'ejercicio' => $defEj,
            'anioFiltro' => $anioFiltro,
            'soloEjercicio' => $soloEjercicio,
            'anios' => $anios,
            'delegadoId' => $request->input('delegado'),
        ]);
    }

    /**
     * Vista catálogo licitación: siempre desde {@see ProductoLicitado} vía producto_licitado_id (todas las asignaciones del filtro).
     *
     * @return array<string, mixed>
     */
    private function asignacionToLineaSoloLicitacion(AsignacionEmpleadoProducto $a): array
    {
        $pl = $a->productoLicitado;

        $cantidad = $a->cantidad;
        $cantidadNum = is_numeric($cantidad) ? (int) $cantidad : 0;

        $unitTotal = (float) ($pl?->precio_unitario ?? 0);
        $n = max(0, $cantidadNum);
        $importeTotal = $n > 0 ? round($unitTotal * $n, 2) : round($unitTotal, 2);

        return [
            'solicitud_id' => $a->id,
            'anio' => $a->anio,
            'no_partida' => $pl?->numero_partida,
            'partida_especifica_codigo' => $pl?->partida_especifica,
            'producto_descripcion' => $pl?->descripcion ?? '—',
            'producto_codigo' => $pl?->codigo_catalogo ?? '—',
            'clave_para_ejercicio_snapshot' => $a->clave_partida_presupuestal,
            'talla' => $a->talla,
            'cantidad' => $cantidad,
            'importe_total' => $importeTotal,
            'estado' => 'licitado',
            'es_sustitucion' => false,
            'clasificacion' => $this->nombreClasificacionDesdeLicitacion($a),
        ];
    }

    /**
     * Con producto_cotizado enlazado: clave, descripción e importe desde esa fila en producto_cotizado; partida desde producto_licitado.
     *
     * @return array<string, mixed>
     */
    private function asignacionToLinea(AsignacionEmpleadoProducto $a, ?ProductoCotizado $productoCotizado = null): array
    {
        $pl = $a->productoLicitado;
        $usaCotizado = $productoCotizado !== null;

        $cantidad = $a->cantidad;
        $cantidadNum = is_numeric($cantidad) ? (int) $cantidad : 0;

        $unitTotal = 0.0;
        if ($usaCotizado) {
            $unitTotal = (float) ($productoCotizado->total ?? $productoCotizado->precio_unitario ?? 0);
        } elseif ($pl !== null) {
            $unitTotal = (float) ($pl->precio_unitario ?? 0);
        }

        $n = max(0, $cantidadNum);
        $importeTotal = $n > 0 ? round($unitTotal * $n, 2) : round($unitTotal, 2);

        return [
            'solicitud_id' => $a->id,
            'anio' => $a->anio,
            'no_partida' => $pl?->numero_partida,
            'partida_especifica_codigo' => $pl?->partida_especifica,
            'producto_descripcion' => $usaCotizado
                ? (string) ($productoCotizado->descripcion ?? '—')
                : ($pl?->descripcion ?? '—'),
            'producto_codigo' => $usaCotizado
                ? ($productoCotizado->clave ?? '—')
                : ($pl?->codigo_catalogo ?? '—'),
            'clave_para_ejercicio_snapshot' => $usaCotizado
                ? ($productoCotizado->clave ?? $productoCotizado->referencia_codigo ?? $a->clave_partida_presupuestal)
                : $a->clave_partida_presupuestal,
            'talla' => $a->talla,
            'cantidad' => $cantidad,
            'importe_total' => $importeTotal,
            'estado' => $usaCotizado ? 'aprobado' : 'en_muestreo',
            'es_sustitucion' => false,
            'clasificacion' => $usaCotizado
                ? $this->nombreClasificacionDesdeCotizado($a, $productoCotizado)
                : $this->nombreClasificacionDesdeLicitacion($a),
        ];
    }

    /**
     * @param  array<string, int>  $map
     * @return list<array{nombre: string, piezas: int}>
     */
    private function resumenClasificacionOrdenado(array $map): array
    {
        $rows = [];
        foreach ($map as $nombre => $piezas) {
            $rows[] = ['nombre' => (string) $nombre, 'piezas' => (int) $piezas];
        }
        usort($rows, fn (array $a, array $b) => strcmp($a['nombre'], $b['nombre']));

        return $rows;
    }

    private function piezasConteoAsignacion(AsignacionEmpleadoProducto $a): int
    {
        if (is_numeric($a->cantidad)) {
            $n = (int) $a->cantidad;

            return $n > 0 ? $n : 1;
        }

        return 1;
    }

    private function nombreClasificacionDesdeCotizado(AsignacionEmpleadoProducto $a, ProductoCotizado $pc): string
    {
        return $pc->clasificacionPrincipal?->nombre
            ?? $a->productoLicitado?->clasificacionPrincipal?->nombre
            ?? 'Sin clasificar';
    }

    private function nombreClasificacionDesdeLicitacion(AsignacionEmpleadoProducto $a): string
    {
        return $a->productoLicitado?->clasificacionPrincipal?->nombre ?? 'Sin clasificar';
    }
}
