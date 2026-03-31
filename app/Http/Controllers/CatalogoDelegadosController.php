<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CatalogoDelegadosController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $delegados = $this->cx()->table('delegados as d')
            ->leftJoin('users as u', 'u.delegado_id', '=', 'd.id')
            ->orderBy('d.nombre_completo')
            ->select([
                'd.id',
                'd.nombre_completo',
                'u.id as user_id',
                'u.name as user_name',
                'u.email as user_email',
                'u.activo as user_activo',
            ])
            ->get();

        $delegacionIdsPorDelegado = $this->cx()->table('delegado_delegacion')
            ->get()
            ->groupBy('delegado_id');

        $delegacionRows = $this->cx()->table('delegaciones')
            ->orderBy('clave')
            ->get()
            ->keyBy('id');

        $empleadosPorDelegado = $this->cx()->table('empleados as e')
            ->join('delegado_delegacion as dd', 'dd.delegacion_id', '=', 'e.delegacion_id')
            ->groupBy('dd.delegado_id')
            ->selectRaw('dd.delegado_id, COUNT(e.id) as c')
            ->pluck('c', 'delegado_id');

        $todasDelIds = collect($delegacionIdsPorDelegado->values())
            ->flatMap(fn ($rows) => collect($rows)->pluck('delegacion_id'))
            ->unique()
            ->values();

        $urPorDelId = $todasDelIds->isEmpty() ? collect() :
            $this->cx()->table('empleados')
                ->whereIn('delegacion_id', $todasDelIds)
                ->whereNotNull('ur')
                ->groupBy(['delegacion_id', 'ur'])
                ->selectRaw('delegacion_id, ur, COUNT(*) as cnt')
                ->get()
                ->groupBy('delegacion_id')
                ->map(fn ($group) => $group->sortByDesc('cnt')->first()?->ur);

        $rows = $delegados->map(function ($d) use ($delegacionIdsPorDelegado, $delegacionRows, $empleadosPorDelegado, $urPorDelId) {
            $ids = collect($delegacionIdsPorDelegado->get($d->id, []))->pluck('delegacion_id')->all();
            $claves = collect($ids)
                ->map(fn ($id) => $delegacionRows->get($id)?->clave)
                ->filter()
                ->values()
                ->all();

            $urPrincipal = collect($ids)
                ->map(fn ($id) => $urPorDelId->get($id))
                ->filter()
                ->first();

            return [
                'id' => $d->id,
                'nombre' => $d->nombre_completo,
                'delegacion' => implode(', ', $claves) ?: '—',
                'delegaciones_ids' => $ids,
                'empleados_count' => (int) ($empleadosPorDelegado[$d->id] ?? 0),
                'user_id' => $d->user_id,
                'user_name' => $d->user_name,
                'user_email' => $d->user_email,
                'user_activo' => $d->user_activo,
                'ur_principal' => $urPrincipal,
            ];
        });

        if ($request->filled('buscar')) {
            $q = mb_strtolower($request->string('buscar')->trim());
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['nombre'] ?? ''), $q) ||
                str_contains(mb_strtolower($r['delegacion'] ?? ''), $q) ||
                str_contains(mb_strtolower($r['user_name'] ?? ''), $q)
            )->values();
        }

        $usuarios = DB::table('users')
            ->orderBy('name')
            ->select(['id', 'name', 'nue', 'email', 'delegado_id', 'activo'])
            ->get();

        $delegaciones = $delegacionRows->values()->map(fn ($d) => [
            'id' => $d->id,
            'clave' => $d->clave,
            'nombre' => $d->nombre,
            'ur_principal' => $urPorDelId->get($d->id),
        ]);

        return Inertia::render('Delegados/Index', [
            'delegados' => $rows->values(),
            'usuarios' => $usuarios,
            'delegaciones' => $delegaciones,
            'filters' => ['buscar' => $request->string('buscar')->toString()],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'delegacion_ids' => 'nullable|array',
            'delegacion_ids.*' => 'integer',
            'modo_usuario' => 'nullable|in:ninguno,existente,nuevo',
            'user_id' => 'nullable|integer|exists:users,id',
            'user_nombre' => 'required_if:modo_usuario,nuevo|nullable|string|max:255',
            'user_rfc' => 'nullable|string|max:20|unique:users,rfc',
            'user_nue' => 'nullable|string|max:20',
            'user_email' => 'nullable|email|max:255|unique:users,email',
            'user_password' => 'required_if:modo_usuario,nuevo|nullable|string|min:8',
        ]);

        $delegadoId = $this->cx()->table('delegados')->insertGetId([
            'nombre_completo' => $validated['nombre_completo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated['delegacion_ids'] ?? [] as $delId) {
            $this->cx()->table('delegado_delegacion')->insertOrIgnore([
                'delegado_id' => $delegadoId,
                'delegacion_id' => $delId,
            ]);
        }

        $modo = $validated['modo_usuario'] ?? 'ninguno';

        if ($modo === 'existente' && ! empty($validated['user_id'])) {
            DB::table('users')
                ->where('id', $validated['user_id'])
                ->update(['delegado_id' => $delegadoId]);
        }

        if ($modo === 'nuevo') {
            User::create([
                'name' => $validated['user_nombre'],
                'rfc' => $validated['user_rfc'] ? strtoupper($validated['user_rfc']) : null,
                'nue' => $validated['user_nue'] ? strtoupper($validated['user_nue']) : null,
                'email' => $validated['user_email'] ? strtolower($validated['user_email']) : null,
                'password' => Hash::make($validated['user_password']),
                'activo' => 1,
                'must_change_password' => 1,
                'delegado_id' => $delegadoId,
            ]);
        }

        return back();
    }

    public function crearUsuario(Request $request, int $delegado)
    {
        $existe = $this->cx()->table('delegados')->where('id', $delegado)->exists();
        abort_if(! $existe, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20|unique:users,rfc',
            'nue' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        DB::table('users')->where('delegado_id', $delegado)->update(['delegado_id' => null]);

        User::create([
            'name' => $validated['name'],
            'rfc' => $validated['rfc'] ? strtoupper($validated['rfc']) : null,
            'nue' => $validated['nue'] ? strtoupper($validated['nue']) : null,
            'email' => $validated['email'] ? strtolower($validated['email']) : null,
            'password' => Hash::make($validated['password']),
            'activo' => 1,
            'must_change_password' => 1,
            'delegado_id' => $delegado,
        ]);

        return back();
    }

    public function asociarUsuario(Request $request, int $delegado)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $existe = $this->cx()->table('delegados')->where('id', $delegado)->exists();
        abort_if(! $existe, 404);

        DB::table('users')
            ->where('delegado_id', $delegado)
            ->update(['delegado_id' => null]);

        DB::table('users')
            ->where('id', $validated['user_id'])
            ->update(['delegado_id' => $delegado]);

        return back();
    }

    public function desasociarUsuario(int $delegado)
    {
        DB::table('users')
            ->where('delegado_id', $delegado)
            ->update(['delegado_id' => null]);

        return back();
    }

    public function show(int $delegado): Response
    {
        $row = $this->cx()->table('delegados')->where('id', $delegado)->first();
        abort_if($row === null, 404);

        $delegacionIds = $this->cx()->table('delegado_delegacion')
            ->where('delegado_id', $delegado)
            ->pluck('delegacion_id');

        $delegaciones = $this->cx()->table('delegaciones')
            ->whereIn('id', $delegacionIds)
            ->orderBy('clave')
            ->get();

        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        $empRows = $this->cx()->table('empleados as e')
            ->leftJoin('dependencias as d', 'd.id', '=', 'e.dependencia_id')
            ->leftJoin('delegaciones as del', 'del.id', '=', 'e.delegacion_id')
            ->whereIn('e.delegacion_id', $delegacionIds)
            ->orderBy('e.apellido_paterno')
            ->orderBy('e.apellido_materno')
            ->orderBy('e.nombre')
            ->select([
                'e.id', 'e.nue', 'e.nombre', 'e.apellido_paterno',
                'e.apellido_materno', 'e.ur',
                'd.nombre as dependencia_nombre',
                'del.clave as delegacion_clave',
            ])
            ->get();

        $empIds = $empRows->pluck('id');
        $productosPorEmpleado = $empIds->isEmpty()
            ? collect()
            : $this->cx()->table('solicitudes_vestuario')
                ->whereIn('empleado_id', $empIds)
                ->where('anio', $ejercicio)
                ->groupBy('empleado_id')
                ->selectRaw('empleado_id, COUNT(*) as c')
                ->pluck('c', 'empleado_id');

        $empleados = $empRows->map(function ($e) use ($productosPorEmpleado) {
            $nombreCompleto = trim(implode(' ', array_filter([
                $e->nombre, $e->apellido_paterno, $e->apellido_materno,
            ])));

            return [
                'id' => $e->id,
                'nombre_completo' => $nombreCompleto ?: '—',
                'nue' => $e->nue,
                'ur' => $e->ur,
                'dependencia_nombre' => $e->dependencia_nombre,
                'delegacion_clave' => $e->delegacion_clave,
                'productos_count' => (int) ($productosPorEmpleado[$e->id] ?? 0),
            ];
        });

        return Inertia::render('Delegados/Show', [
            'delegado' => ['id' => $row->id, 'nombre_completo' => $row->nombre_completo],
            'delegaciones' => $delegaciones,
            'empleados' => $empleados,
            'ejercicio' => $ejercicio,
        ]);
    }
}
