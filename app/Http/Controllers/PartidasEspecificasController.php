<?php

namespace App\Http\Controllers;

use App\Models\PartidaEspecifica;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartidasEspecificasController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    /**
     * Consulta base con filtros año, partida y búsqueda (sin paginar).
     */
    private function basePeQuery(Request $request, int $anio, ?int $partidaFiltro): Builder
    {
        $q = $this->cx()->table('partidas_especificas as pe')
            ->join('partidas as pa', 'pa.id', '=', 'pe.partida_id')
            ->where('pe.anio', $anio);

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

        return $q;
    }

    /**
     * Un registro representativo por cada valor distinto de `clave` (MIN(id)).
     *
     * @return Collection<int, int>
     */
    private function idsRepresentativosPorClaveUnica(Request $request, int $anio, ?int $partidaFiltro): Collection
    {
        return $this->basePeQuery($request, $anio, $partidaFiltro)
            ->selectRaw('MIN(pe.id) as id')
            ->groupBy('pe.clave')
            ->pluck('id');
    }

    private function listadoQuery(Request $request, int $anio, ?int $partidaFiltro, bool $soloClavesUnicas): Builder
    {
        $q = $this->basePeQuery($request, $anio, $partidaFiltro);

        if ($soloClavesUnicas) {
            $ids = $this->idsRepresentativosPorClaveUnica($request, $anio, $partidaFiltro);
            $q->whereIn('pe.id', $ids->all());
        }

        return $q->orderBy('pa.no_partida')
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
    }

    public function index(Request $request): InertiaResponse
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);
        $anio = (int) $request->query('anio', $ejercicio);

        $rawPartida = $request->query('partida_id');
        $partidaFiltro = ($rawPartida !== null && $rawPartida !== '' && (int) $rawPartida > 0)
            ? (int) $rawPartida
            : null;

        $soloClavesUnicas = $request->query('unicas', '1') !== '0';

        $q = $this->listadoQuery($request, $anio, $partidaFiltro, $soloClavesUnicas);

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
                'clave_partida' => $row->clave_partida,
                'no_partida' => $row->no_partida,
                'partida_descripcion' => $row->partida_descripcion,
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
                'unicas' => $soloClavesUnicas,
            ],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);
        $anio = (int) $request->query('anio', $ejercicio);

        $rawPartida = $request->query('partida_id');
        $partidaFiltro = ($rawPartida !== null && $rawPartida !== '' && (int) $rawPartida > 0)
            ? (int) $rawPartida
            : null;

        $soloClavesUnicas = $request->query('unicas', '1') !== '0';

        $rows = $this->listadoQuery($request, $anio, $partidaFiltro, $soloClavesUnicas)->get();

        $ids = $rows->pluck('id');
        $productosPorPe = $ids->isEmpty()
            ? collect()
            : $this->cx()->table('solicitudes_vestuario')
                ->whereIn('partida_especifica_id', $ids)
                ->where('anio', $anio)
                ->groupBy('partida_especifica_id')
                ->selectRaw('partida_especifica_id, COUNT(*) as c')
                ->pluck('c', 'partida_especifica_id');

        $filename = 'lineas_presupuestales_'.$anio.'_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($rows, $productosPorPe, $anio) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'id',
                'clave',
                'descripcion',
                'clave_partida',
                'anio',
                'no_partida',
                'partida_descripcion',
                'solicitudes_count',
            ], ',', '"', '');

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->id,
                    $row->clave,
                    $row->descripcion,
                    $row->clave_partida,
                    $row->anio,
                    $row->no_partida,
                    $row->partida_descripcion,
                    (int) ($productosPorPe[$row->id] ?? 0),
                ], ',', '"', '');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
