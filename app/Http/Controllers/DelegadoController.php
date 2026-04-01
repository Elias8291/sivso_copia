<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use App\Models\Delegacion;
use App\Models\Delegado;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class DelegadoController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $delegacionF = (string) $request->input('delegacion', '');

        $query = Delegado::query()->with(['delegaciones']);

        if ($buscar !== '') {
            $like = '%'.$buscar.'%';
            $query->where(function ($q) use ($like) {
                $q->where('nombre_completo', 'like', $like)
                    ->orWhere('nue', 'like', $like);
            });
        }

        if ($delegacionF !== '' && $delegacionF !== 'Todas') {
            $query->whereHas('delegaciones', fn ($q) => $q->where('codigo', $delegacionF));
        }

        $paginator = $query
            ->orderBy('nombre_completo')
            ->paginate(20)
            ->withQueryString();

        $ids = $paginator->getCollection()->pluck('id')->all();
        $codesByDelegado = [];
        if ($ids !== []) {
            foreach (
                DB::table('delegado_delegacion')->whereIn('delegado_id', $ids)->get() as $row
            ) {
                $codesByDelegado[$row->delegado_id][] = $row->delegacion_codigo;
            }
        }

        $allCodes = collect($codesByDelegado)->flatten()->unique()->filter()->values()->all();
        $empByCode = [];
        if ($allCodes !== []) {
            $empByCode = Empleado::query()
                ->selectRaw('delegacion_codigo, count(*) as c')
                ->whereIn('delegacion_codigo', $allCodes)
                ->groupBy('delegacion_codigo')
                ->pluck('c', 'delegacion_codigo')
                ->all();
        }

        $delegados = $paginator->through(function (Delegado $d) use ($codesByDelegado, $empByCode) {
            $codes = $codesByDelegado[$d->id] ?? [];
            $emp = 0;
            foreach ($codes as $c) {
                $emp += (int) ($empByCode[$c] ?? 0);
            }

            $labels = $d->delegaciones->pluck('codigo')->filter()->values();
            $delegacionStr = $labels->isNotEmpty() ? $labels->implode(', ') : '—';

            return [
                'id' => $d->id,
                'nombre' => $d->nombre_completo,
                'nombre_completo' => $d->nombre_completo,
                'nue' => $d->nue,
                'delegacion' => $delegacionStr,
                'empleados_count' => $emp,
                'user_id' => null,
                'user_name' => null,
                'user_activo' => false,
            ];
        });

        $delegacionesCatalogo = Delegacion::query()
            ->orderBy('codigo')
            ->get(['codigo', 'ur_referencia'])
            ->map(fn (Delegacion $d) => [
                'id' => $d->codigo,
                'clave' => $d->codigo,
                'nombre' => $d->codigo,
                'ur_principal' => $d->ur_referencia,
            ])
            ->values()
            ->all();

        $usuarios = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'nue'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'nue' => $u->nue,
                'delegado_id' => null,
            ])
            ->values()
            ->all();

        return Inertia::render('Delegados/Index', [
            'delegados' => $delegados,
            'usuarios' => $usuarios,
            'delegaciones' => $delegacionesCatalogo,
            'filters' => [
                'buscar' => $buscar,
                'delegacion' => $delegacionF !== '' ? $delegacionF : 'Todas',
            ],
        ]);
    }

    public function show(Request $request, Delegado $delegado): Response
    {
        $ej = $this->ejercicio($request);

        $delegado->load('delegaciones');
        $codigos = $delegado->delegaciones->pluck('codigo')->filter()->values()->all();

        $empleados = collect();
        if ($codigos !== []) {
            $empleados = Empleado::query()
                ->with(['dependencia', 'delegacion'])
                ->whereIn('delegacion_codigo', $codigos)
                ->withCount([
                    'asignaciones as productos_count' => fn ($q) => $q->where('anio', $ej),
                ])
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->get()
                ->map(static function (Empleado $e) {
                    $nombre = trim(implode(' ', array_filter([
                        $e->nombre,
                        $e->apellido_paterno,
                        $e->apellido_materno,
                    ])));

                    return [
                        'id' => $e->id,
                        'nombre_completo' => $nombre !== '' ? $nombre : '—',
                        'nue' => $e->nue,
                        'dependencia_nombre' => $e->dependencia?->nombre,
                        'delegacion_clave' => $e->delegacion_codigo,
                        'productos_count' => (int) ($e->productos_count ?? 0),
                    ];
                });
        }

        return Inertia::render('Delegados/Show', [
            'delegado' => [
                'id' => $delegado->id,
                'nombre_completo' => $delegado->nombre_completo,
                'nue' => $delegado->nue,
                'user_id' => null,
                'user_email' => null,
            ],
            'delegaciones' => $delegado->delegaciones->map(static fn (Delegacion $d) => [
                'id' => $d->codigo,
                'clave' => $d->codigo,
                'codigo' => $d->codigo,
            ])->values()->all(),
            'empleados' => $empleados->values()->all(),
            'ejercicio' => $ej,
        ]);
    }
}
