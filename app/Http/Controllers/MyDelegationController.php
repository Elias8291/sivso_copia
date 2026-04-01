<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use App\Models\AsignacionEmpleadoProducto;
use App\Models\ClasificacionBien;
use App\Models\Delegacion;
use App\Models\Delegado;
use App\Models\Dependencia;
use App\Models\Empleado;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class MyDelegationController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $ej = $this->ejercicio($request);
        $codigosPermitidos = $user instanceof User ? $this->delegacionCodigosPermitidos($user) : [];

        $delegacionesOpts = $this->delegacionesOptions($codigosPermitidos);
        $requested = (string) $request->input('delegacion', '');
        $activa = ($requested !== '' && in_array($requested, $codigosPermitidos, true))
            ? $requested
            : ($codigosPermitidos[0] ?? null);

        $delegationName = $activa ?? '—';
        if ($activa !== null) {
            $match = collect($delegacionesOpts)->firstWhere('id', $activa);
            if ($match && ($match['nombre'] ?? '') !== '') {
                $delegationName = $match['clave'].' — '.$match['nombre'];
            } else {
                $delegationName = $activa;
            }
        }

        $employees = [];
        if ($activa !== null) {
            $rows = Empleado::query()
                ->with(['dependencia', 'delegacion'])
                ->where('delegacion_codigo', $activa)
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->get();

            foreach ($rows as $e) {
                [$items, $selections] = $this->buildWardrobePayload($e, $ej);
                $nombreListado = trim(implode(' ', array_filter([
                    $e->nombre,
                    $e->apellido_paterno,
                    $e->apellido_materno,
                ])));
                $employees[] = [
                    'id' => $e->id,
                    'name' => $nombreListado !== '' ? $nombreListado : '—',
                    'nue' => (string) ($e->nue ?? ''),
                    'dependencia' => $e->dependencia?->nombre ?? '—',
                    'delegacion' => $e->delegacion_codigo,
                    'position' => '—',
                    'ur' => $e->ur,
                    'wardrobeItems' => $items,
                    'selections' => $selections,
                    'status' => $this->computeWardrobeStatus($items, $selections),
                ];
            }
        }

        $dependencias = Dependencia::query()
            ->orderBy('nombre')
            ->get(['ur', 'nombre'])
            ->map(fn (Dependencia $d) => [
                'ur' => $d->ur,
                'nombre' => $d->nombre,
            ])
            ->values()
            ->all();

        $delegacionesPorUr = $this->delegacionesPorUrMap($codigosPermitidos);

        return Inertia::render('MyDelegation/Index', [
            'employees' => $employees,
            'delegation_name' => $delegationName,
            'delegaciones' => $delegacionesOpts,
            'delegacion_activa_id' => $activa,
            'ejercicio' => $ej,
            'bajas' => ['total' => 0, 'importe' => 0],
            'dependencias' => $dependencias,
            'delegaciones_por_ur' => $delegacionesPorUr,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }
        $ej = $this->ejercicio($request);
        $empleado = Empleado::query()
            ->with(['dependencia', 'delegacion'])
            ->findOrFail((int) $id);

        $permitidos = $this->delegacionCodigosPermitidos($user);
        if ($permitidos !== [] && ! in_array($empleado->delegacion_codigo, $permitidos, true)) {
            abort(403);
        }

        $asignaciones = AsignacionEmpleadoProducto::query()
            ->where('empleado_id', $empleado->id)
            ->where('anio', $ej)
            ->with(['productoLicitado.clasificacionPrincipal'])
            ->orderBy('id')
            ->get();

        $wardrobeItems = [];
        foreach ($asignaciones as $a) {
            $pl = $a->productoLicitado;
            if ($pl === null) {
                continue;
            }
            $sizes = $this->opcionesTallas($pl->medida, $a->talla);
            $wardrobeItems[] = [
                'id' => $a->id,
                'name' => $pl->descripcion ?? 'Producto',
                'description' => trim(implode(' · ', array_filter([
                    $pl->codigo_catalogo,
                    $pl->partida_especifica ? 'Partida '.$pl->partida_especifica : null,
                ]))),
                'type' => $this->tipoVestuarioFromClasificacion($pl->clasificacionPrincipal),
                'sizes' => $sizes,
                'current_size' => $a->talla !== null && $a->talla !== '' ? (string) $a->talla : '',
            ];
        }

        $nombreCompleto = trim(implode(' ', array_filter([
            $empleado->nombre,
            $empleado->apellido_paterno,
            $empleado->apellido_materno,
        ])));

        $periodo = Periodo::query()
            ->where('anio', $ej)
            ->where('estado', Periodo::ESTADO_ABIERTO)
            ->orderByDesc('id')
            ->first();
        $deadline = $periodo?->fecha_fin !== null
            ? $periodo->fecha_fin->format('d/m/Y')
            : 'fin del ejercicio '.$ej;

        return Inertia::render('MyDelegation/Show', [
            'employee' => [
                'id' => (string) $empleado->id,
                'name' => $nombreCompleto !== '' ? $nombreCompleto : '—',
                'nue' => (string) ($empleado->nue ?? ''),
                'dependencia' => $empleado->dependencia?->nombre ?? '—',
                'delegacion' => $empleado->delegacion_codigo,
                'department' => $empleado->dependencia?->nombre ?? '—',
                'position' => '—',
                'deadline' => $deadline,
            ],
            'wardrobeItems' => $wardrobeItems,
            'ejercicio' => $ej,
        ]);
    }

    public function saveTallas(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleado,id'],
            'tallas' => ['required', 'array'],
            'tallas.*' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }
        $empleado = Empleado::query()->findOrFail($validated['empleado_id']);
        $permitidos = $this->delegacionCodigosPermitidos($user);
        if ($permitidos !== [] && ! in_array($empleado->delegacion_codigo, $permitidos, true)) {
            abort(403);
        }

        foreach ($validated['tallas'] as $asignacionId => $talla) {
            $asignacionId = (int) $asignacionId;
            if ($asignacionId <= 0) {
                continue;
            }
            $a = AsignacionEmpleadoProducto::query()
                ->where('empleado_id', $empleado->id)
                ->whereKey($asignacionId)
                ->first();
            if ($a === null) {
                continue;
            }
            $v = is_string($talla) ? trim($talla) : '';
            $a->talla = $v === '' ? null : $v;
            $a->save();
        }

        return back();
    }

    /**
     * @return list<string>
     */
    private function delegacionCodigosPermitidos(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $delegado = $this->delegadoForUser($user);
        if ($delegado !== null) {
            return $delegado->delegaciones
                ->pluck('codigo')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if ($user->isSuperAdmin() || $user->isSivsoAdministrator()) {
            return Delegacion::query()
                ->orderBy('codigo')
                ->pluck('codigo')
                ->all();
        }

        return [];
    }

    private function delegadoForUser(User $user): ?Delegado
    {
        $nue = trim((string) ($user->nue ?? ''));
        if ($nue === '') {
            return null;
        }

        return Delegado::query()
            ->with('delegaciones')
            ->where('nue', $nue)
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  list<string>  $codigos
     * @return list<array{id: string, clave: string, nombre: string}>
     */
    private function delegacionesOptions(array $codigos): array
    {
        if ($codigos === []) {
            return [];
        }

        $models = Delegacion::query()
            ->with('dependenciaReferencia')
            ->whereIn('codigo', $codigos)
            ->orderBy('codigo')
            ->get()
            ->keyBy('codigo');

        $out = [];
        foreach ($codigos as $codigo) {
            $d = $models->get($codigo);
            $out[] = [
                'id' => $codigo,
                'clave' => $codigo,
                'nombre' => $d?->dependenciaReferencia?->nombre ?? '',
            ];
        }

        return $out;
    }

    /**
     * @param  list<string>  $codigosDelegacion
     */
    private function delegacionesPorUrMap(array $codigosDelegacion): object
    {
        $out = new \stdClass;
        if ($codigosDelegacion === []) {
            return $out;
        }

        $rows = DB::table('dependencia_delegacion')
            ->whereIn('delegacion_codigo', $codigosDelegacion)
            ->orderBy('ur')
            ->orderBy('delegacion_codigo')
            ->get(['ur', 'delegacion_codigo']);

        /** @var array<string, list<array{id: string, clave: string, nombre: string}>> $map */
        $map = [];
        foreach ($rows as $row) {
            $ur = (string) $row->ur;
            $codigo = (string) $row->delegacion_codigo;
            $map[$ur] ??= [];
            $map[$ur][] = [
                'id' => $codigo,
                'clave' => $codigo,
                'nombre' => '',
            ];
        }

        foreach ($map as $ur => $list) {
            $out->{$ur} = $list;
        }

        return $out;
    }

    /**
     * @return array{0: list<array<string, mixed>>, 1: array<int|string, string>}
     */
    private function buildWardrobePayload(Empleado $e, int $ejercicio): array
    {
        $asignaciones = AsignacionEmpleadoProducto::query()
            ->where('empleado_id', $e->id)
            ->where('anio', $ejercicio)
            ->with(['productoLicitado.clasificacionPrincipal'])
            ->orderBy('id')
            ->get();

        $items = [];
        $selections = [];
        foreach ($asignaciones as $a) {
            $pl = $a->productoLicitado;
            if ($pl === null) {
                continue;
            }
            $sizes = $this->opcionesTallas($pl->medida, $a->talla);
            $items[] = [
                'id' => $a->id,
                'name' => $pl->descripcion ?? 'Producto',
                'description' => trim(implode(' · ', array_filter([
                    $pl->codigo_catalogo,
                    $pl->partida_especifica ? 'Partida '.$pl->partida_especifica : null,
                ]))),
                'type' => $this->tipoVestuarioFromClasificacion($pl->clasificacionPrincipal),
                'sizes' => $sizes,
                'price' => $this->importeAsignacion($a),
            ];
            $selections[$a->id] = $a->talla !== null && $a->talla !== '' ? (string) $a->talla : '';
        }

        return [$items, $selections];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<int|string, string>  $selections
     */
    private function computeWardrobeStatus(array $items, array $selections): string
    {
        if ($items === []) {
            return 'Pendiente';
        }
        $total = count($items);
        $filled = 0;
        foreach ($items as $i) {
            $id = $i['id'];
            if (($selections[$id] ?? '') !== '') {
                $filled++;
            }
        }
        if ($filled === $total) {
            return 'Completado';
        }
        if ($filled > 0) {
            return 'En progreso';
        }

        return 'Pendiente';
    }

    private function importeAsignacion(AsignacionEmpleadoProducto $a): float
    {
        $pl = $a->productoLicitado;
        $cantidadNum = is_numeric($a->cantidad) ? (int) $a->cantidad : 0;
        $unit = (float) ($pl?->precio_unitario ?? 0);
        $n = max(0, $cantidadNum);

        return $n > 0 ? round($unit * $n, 2) : round($unit, 2);
    }

    private function tipoVestuarioFromClasificacion(?ClasificacionBien $c): string
    {
        if ($c === null) {
            return 'Prenda';
        }
        $cod = strtoupper((string) $c->codigo);

        return match (true) {
            in_array($cod, ['ZAPATO', 'BOTA', 'SANDALIA'], true) => 'Calzado',
            in_array($cod, ['PANTALON', 'FALDA'], true) => 'Prenda Inferior',
            default => 'Prenda Superior',
        };
    }

    /**
     * @return list<string>
     */
    private function opcionesTallas(?string $medidaProducto, ?string $tallaActual): array
    {
        $opts = ['28', '30', '32', '34', '36', '38', '40', '42', '44', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Única', '—'];
        foreach ([$medidaProducto, $tallaActual] as $v) {
            $v = trim((string) $v);
            if ($v !== '' && ! in_array($v, $opts, true)) {
                array_unshift($opts, $v);
            }
        }

        return array_values(array_unique($opts));
    }
}
