<?php

namespace App\Http\Controllers;

use App\Models\Delegado;
use App\Models\SolicitudVestuario;
use Illuminate\Database\Connection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MyDelegationController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    /**
     * Delegaciones del delegado vinculadas a dependencias con la UR indicada.
     */
    private function delegacionIdsParaUr(?int $ur, Collection $delegacionIdsManaged): Collection
    {
        if ($ur === null) {
            return $delegacionIdsManaged->values();
        }

        $depIds = $this->cx()->table('dependencias')->where('ur', $ur)->pluck('id');
        if ($depIds->isEmpty()) {
            return $delegacionIdsManaged->values();
        }

        $ids = $this->cx()->table('dependencia_delegacion')
            ->whereIn('dependencia_id', $depIds)
            ->whereIn('delegacion_id', $delegacionIdsManaged)
            ->pluck('delegacion_id')
            ->unique()
            ->values();

        return $ids->isNotEmpty() ? $ids : $delegacionIdsManaged->values();
    }

    private const TALLAS = [
        'XCH', 'CH', 'M', 'G', 'XG', 'XXG',
        '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32',
    ];

    public function index(Request $request): Response
    {
        $user = auth()->user();
        $delegado = Delegado::find($user->delegado_id);

        if (! $delegado) {
            abort(403, 'No tienes acceso a una delegación');
        }

        $delegaciones = $delegado->delegaciones()->orderBy('clave')->get();

        if ($delegaciones->isEmpty()) {
            abort(403, 'El delegado no tiene delegaciones asignadas');
        }

        $delegacionId = (int) $request->query('delegacion', $delegaciones->first()->id);
        $delegacionActiva = $delegaciones->firstWhere('id', $delegacionId) ?? $delegaciones->first();

        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        $empleados = $this->cx()->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->where('e.delegacion_id', $delegacionActiva->id)
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->select([
                'e.id', 'e.nue', 'e.nombre', 'e.apellido_paterno',
                'e.apellido_materno', 'e.ur',
                'd.nombre as dependencia_nombre',
            ])
            ->get();

        $empIds = $empleados->pluck('id');

        $solicitudes = $empIds->isEmpty()
            ? collect()
            : $this->cx()->table('solicitudes_vestuario as s')
                ->join('productos as p', 'p.id', '=', 's.producto_id')
                ->leftJoin('partidas as pa', 'pa.id', '=', 'p.partida_id')
                ->leftJoin('producto_precios as pp', function ($j) use ($ejercicio) {
                    $j->on('pp.producto_id', '=', 'p.id')->where('pp.anio', '=', $ejercicio);
                })
                ->whereIn('s.empleado_id', $empIds)
                ->where('s.anio', $ejercicio)
                ->orderBy('pa.no_partida')
                ->orderBy('p.descripcion')
                ->select([
                    's.id as solicitud_id',
                    's.empleado_id',
                    's.producto_id',
                    's.talla',
                    's.cantidad',
                    's.precio_unitario',
                    's.importe_total',
                    's.estado',
                    's.es_sustitucion',
                    'p.descripcion as producto_descripcion',
                    'p.marca',
                    'p.medida',
                    'pa.descripcion as partida_descripcion',
                    'pp.precio_unitario as precio_actual',
                ])
                ->get()
                ->groupBy('empleado_id');

        $employees = $empleados->map(function ($emp) use ($solicitudes) {
            $nombre = trim(implode(' ', array_filter([
                $emp->nombre, $emp->apellido_paterno, $emp->apellido_materno,
            ])));

            $empSolicitudes = $solicitudes->get($emp->id, collect());

            $wardrobeItems = $empSolicitudes->map(fn ($s) => [
                'id' => $s->solicitud_id,
                'producto_id' => $s->producto_id,
                'name' => $s->producto_descripcion ?? '—',
                'type' => $s->partida_descripcion ?? 'General',
                'price' => (float) ($s->precio_actual ?? $s->precio_unitario ?? 0),
                'description' => collect([$s->marca, $s->medida])->filter()->implode(' · ') ?: null,
                'sizes' => self::TALLAS,
                'estado' => $s->estado,
                'es_sustitucion' => (bool) $s->es_sustitucion,
            ])->values()->all();

            $selections = [];
            foreach ($empSolicitudes as $s) {
                $selections[$s->solicitud_id] = $s->talla ?? '';
            }

            $total = count($wardrobeItems);
            $filled = count(array_filter($selections));
            $allBaja = $total > 0 && $empSolicitudes->every(fn ($s) => $s->estado === 'baja');

            if ($allBaja) {
                $status = 'Baja';
            } elseif ($total === 0) {
                $status = 'Pendiente';
            } elseif ($filled === $total) {
                $status = 'Completado';
            } elseif ($filled > 0) {
                $status = 'En progreso';
            } else {
                $status = 'Pendiente';
            }

            return [
                'id' => $emp->id,
                'name' => $nombre ?: '—',
                'nue' => $emp->nue,
                'ur' => $emp->ur,
                'dependencia' => $emp->dependencia_nombre,
                'wardrobeItems' => $wardrobeItems,
                'selections' => (object) $selections,
                'status' => $status,
            ];
        })->values()->all();

        $bajasStats = $this->cx()->table('solicitudes_vestuario')
            ->whereIn('empleado_id', $empIds)
            ->where('anio', $ejercicio)
            ->where('estado', 'baja')
            ->selectRaw('COUNT(*) as total_bajas, COALESCE(SUM(importe_total), 0) as importe_bajas')
            ->first();

        $dependencias = $this->cx()->table('dependencia_delegacion as dd')
            ->join('dependencias as dep', 'dep.id', '=', 'dd.dependencia_id')
            ->where('dd.delegacion_id', $delegacionActiva->id)
            ->orderBy('dep.ur')
            ->select(['dep.id', 'dep.ur', 'dep.nombre'])
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'ur' => $d->ur,
                'nombre' => $d->nombre,
            ])->values();

        $delegacionIdsManaged = $delegaciones->pluck('id');

        $urs = $dependencias->pluck('ur')
            ->merge($empleados->pluck('ur'))
            ->filter(fn ($u) => $u !== null && $u !== '')
            ->unique()
            ->values();

        $delegacionesPorUr = [];
        if ($urs->isNotEmpty()) {
            $depIdsPorUr = $this->cx()->table('dependencias')
                ->whereIn('ur', $urs)
                ->select(['id', 'ur'])
                ->get()
                ->groupBy('ur')
                ->map(fn ($rows) => $rows->pluck('id'));

            foreach ($depIdsPorUr as $ur => $depIds) {
                $dels = $this->cx()->table('dependencia_delegacion as dd')
                    ->join('delegaciones as del', 'del.id', '=', 'dd.delegacion_id')
                    ->whereIn('dd.dependencia_id', $depIds)
                    ->whereIn('del.id', $delegacionIdsManaged)
                    ->select(['del.id', 'del.clave', 'del.nombre'])
                    ->distinct()
                    ->orderBy('del.clave')
                    ->get()
                    ->map(fn ($d) => [
                        'id' => $d->id,
                        'clave' => $d->clave,
                        'nombre' => $d->nombre,
                    ])->values()->all();

                $delegacionesPorUr[(string) $ur] = $dels;
            }
        }

        return Inertia::render('MyDelegation/Index', [
            'employees' => $employees,
            'delegation_name' => $delegacionActiva->nombre ?? $delegacionActiva->clave,
            'delegaciones' => $delegaciones->map(fn ($d) => [
                'id' => $d->id,
                'clave' => $d->clave,
                'nombre' => $d->nombre,
            ])->values(),
            'delegacion_activa_id' => $delegacionActiva->id,
            'ejercicio' => $ejercicio,
            'bajas' => [
                'total' => (int) $bajasStats->total_bajas,
                'importe' => (float) $bajasStats->importe_bajas,
            ],
            'dependencias' => $dependencias->all(),
            'delegaciones_por_ur' => $delegacionesPorUr,
        ]);
    }

    public function saveTallas(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $delegado = Delegado::find($user->delegado_id);
        abort_if(! $delegado, 403);

        $validated = $request->validate([
            'empleado_id' => 'required|integer',
            'tallas' => 'required|array',
            'tallas.*' => 'nullable|string|max:10',
        ]);

        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        $delegacionIds = $delegado->delegaciones()->pluck('delegaciones.id');

        $empleado = $this->cx()->table('empleados')
            ->where('id', $validated['empleado_id'])
            ->whereIn('delegacion_id', $delegacionIds)
            ->first();

        abort_if(! $empleado, 403, 'Empleado no pertenece a tus delegaciones');

        $solicitudIds = array_keys($validated['tallas']);

        $solicitudes = SolicitudVestuario::whereIn('id', $solicitudIds)
            ->where('empleado_id', $validated['empleado_id'])
            ->where('anio', $ejercicio)
            ->get();

        foreach ($solicitudes as $solicitud) {
            $talla = $validated['tallas'][$solicitud->id] ?? null;
            if ($talla !== null) {
                $solicitud->talla = $talla ?: null;
                $solicitud->save();
            }
        }

        return back();
    }

    public function baja(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $delegado = Delegado::find($user->delegado_id);
        abort_if(! $delegado, 403);

        $validated = $request->validate([
            'empleado_id' => 'required|integer',
            'tipo' => 'required|in:sin_reemplazo,misma_ur,otra_ur',
            'delegacion_destino_id' => 'nullable|integer',
            'reemplazo' => 'required_unless:tipo,sin_reemplazo|array',
            'reemplazo.nombre' => 'required_unless:tipo,sin_reemplazo|string|max:255',
            'reemplazo.apellido_paterno' => 'required_unless:tipo,sin_reemplazo|string|max:255',
            'reemplazo.apellido_materno' => 'nullable|string|max:255',
            'reemplazo.nue' => 'nullable|string|max:50',
        ]);

        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);
        $delegacionIds = $delegado->delegaciones()->pluck('delegaciones.id');

        $empleado = $this->cx()->table('empleados')
            ->where('id', $validated['empleado_id'])
            ->whereIn('delegacion_id', $delegacionIds)
            ->first();

        abort_if(! $empleado, 403, 'Empleado no pertenece a tus delegaciones');

        $tipo = $validated['tipo'];

        if ($tipo === 'sin_reemplazo') {
            $this->cx()->table('solicitudes_vestuario')
                ->where('empleado_id', $empleado->id)
                ->where('anio', $ejercicio)
                ->update(['estado' => 'baja']);

            return back();
        }

        $reemplazo = $validated['reemplazo'];
        $delegacionDestinoId = (int) ($validated['delegacion_destino_id'] ?? $empleado->delegacion_id);

        $permitidas = $this->delegacionIdsParaUr(
            $empleado->ur !== null ? (int) $empleado->ur : null,
            $delegacionIds
        );

        if (! $permitidas->contains($delegacionDestinoId)) {
            throw ValidationException::withMessages([
                'delegacion_destino_id' => 'Selecciona una delegación de la UR de este empleado.',
            ]);
        }

        $nuevoEmpleadoId = $this->cx()->table('empleados')->insertGetId([
            'nombre' => strtoupper(trim($reemplazo['nombre'])),
            'apellido_paterno' => strtoupper(trim($reemplazo['apellido_paterno'])),
            'apellido_materno' => strtoupper(trim($reemplazo['apellido_materno'] ?? '')),
            'nue' => $reemplazo['nue'] ?: null,
            'delegacion_id' => $delegacionDestinoId,
            'dependencia_id' => $tipo === 'misma_ur' ? $empleado->dependencia_id : null,
            'ur' => $tipo === 'misma_ur' ? $empleado->ur : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($tipo === 'misma_ur') {
            $this->cx()->table('solicitudes_vestuario')
                ->where('empleado_id', $empleado->id)
                ->where('anio', $ejercicio)
                ->update([
                    'empleado_id' => $nuevoEmpleadoId,
                    'talla' => null,
                    'estado' => 'borrador',
                    'updated_at' => now(),
                ]);
        } else {
            $this->cx()->table('solicitudes_vestuario')
                ->where('empleado_id', $empleado->id)
                ->where('anio', $ejercicio)
                ->update(['estado' => 'baja']);
        }

        return back();
    }

    public function show($id): RedirectResponse
    {
        return redirect()->route('my-delegation.index');
    }
}
