<?php

namespace App\Http\Controllers;

use App\Models\PartidaEspecifica;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PartidasEspecificasController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);
        $anio = (int) $request->query('anio', $ejercicio);

        $rawPartida = $request->query('partida_id');
        $partidaFiltro = ($rawPartida !== null && $rawPartida !== '' && (int) $rawPartida > 0)
            ? (int) $rawPartida
            : null;

        $q = $this->cx()->table('partidas_especificas as pe')
            ->join('partidas as pa', 'pa.id', '=', 'pe.partida_id')
            ->where('pe.anio', $anio)
            ->orderBy('pa.no_partida')
            ->orderBy('pe.clave')
            ->select([
                'pe.id',
                'pe.partida_id',
                'pe.anio',
                'pe.clave',
                'pe.descripcion',
                'pe.clave_partida',
                'pa.no_partida',
                'pa.descripcion as partida_descripcion',
            ]);

        if ($partidaFiltro) {
            $q->where('pe.partida_id', $partidaFiltro);
        }

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('pe.clave', 'like', $term)
                    ->orWhere('pe.descripcion', 'like', $term)
                    ->orWhere('pe.clave_partida', 'like', $term)
                    ->orWhere('pa.no_partida', 'like', $term);
            });
        }

        $paginator = $q->paginate(50)->withQueryString();

        $ids = $paginator->getCollection()->pluck('id');
        $productosPorPe = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('solicitudes_vestuario')
                ->whereIn('partida_especifica_id', $ids)
                ->where('anio', $anio)
                ->groupBy('partida_especifica_id')
                ->selectRaw('partida_especifica_id, COUNT(*) as c')
                ->pluck('c', 'partida_especifica_id');

        $paginator->setCollection(
            $paginator->getCollection()->map(fn ($row) => [
                'id' => $row->id,
                'partida_id' => $row->partida_id,
                'anio' => $row->anio,
                'clave' => $row->clave,
                'descripcion' => $row->descripcion,
                'productos_count' => (int) ($productosPorPe[$row->id] ?? 0),
            ])
        );

        $partidas = $this->cx()->table('partidas')
            ->orderBy('no_partida')
            ->select(['id', 'no_partida', 'descripcion'])
            ->get();

        $aniosDisponibles = $this->cx()->table('partidas_especificas')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio')
            ->map(fn ($a) => (int) $a);

        if (! $aniosDisponibles->contains($ejercicio)) {
            $aniosDisponibles->prepend($ejercicio);
            $aniosDisponibles = $aniosDisponibles->sortDesc()->values();
        }

        return Inertia::render('PartidasEspecificas/Index', [
            'partidas_especificas' => $paginator,
            'partidas' => $partidas,
            'ejercicio' => $ejercicio,
            'anio' => $anio,
            'anios_disponibles' => $aniosDisponibles,
            'filters' => [
                'buscar' => $request->string('buscar')->toString(),
                'partida_id' => $partidaFiltro,
                'anio' => $anio,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partida_id' => 'required|integer',
            'anio' => 'required|integer|min:2020|max:2040',
            'clave' => 'required|string|max:512',
            'descripcion' => 'nullable|string|max:5000',
            'clave_partida' => 'nullable|string|max:512',
        ]);

        PartidaEspecifica::create($validated);

        return back();
    }

    public function update(Request $request, int $id)
    {
        $pe = PartidaEspecifica::findOrFail($id);

        $validated = $request->validate([
            'partida_id' => 'required|integer',
            'anio' => 'required|integer|min:2020|max:2040',
            'clave' => 'required|string|max:512',
            'descripcion' => 'nullable|string|max:5000',
            'clave_partida' => 'nullable|string|max:512',
        ]);

        $pe->update($validated);

        return back();
    }

    public function destroy(int $id)
    {
        $pe = PartidaEspecifica::findOrFail($id);

        $hasSolicitudes = $this->cx()->table('solicitudes_vestuario')
            ->where('partida_especifica_id', $id)
            ->exists();

        if ($hasSolicitudes) {
            return back()->withErrors(['general' => 'No se puede eliminar: tiene solicitudes asociadas.']);
        }

        $pe->delete();

        return back();
    }
}
