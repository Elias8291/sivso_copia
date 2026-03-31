<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PartidasController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(Request $request): Response
    {
        $q = $this->cx()->table('partidas')
            ->orderBy('no_partida')
            ->select(['id', 'no_partida', 'descripcion', 'created_at', 'updated_at']);

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $q->where(function ($w) use ($term) {
                $w->where('no_partida', 'like', $term)
                    ->orWhere('descripcion', 'like', $term);
            });
        }

        $partidas = $q->get();

        $productosPorPartida = $this->cx()->table('productos')
            ->groupBy('partida_id')
            ->selectRaw('partida_id, COUNT(*) as c')
            ->pluck('c', 'partida_id');

        $rows = $partidas->map(fn ($row) => [
            'id' => $row->id,
            'no_partida' => $row->no_partida,
            'descripcion' => $row->descripcion,
            'productos_count' => (int) ($productosPorPartida[$row->id] ?? 0),
        ]);

        return Inertia::render('Partidas/Index', [
            'partidas' => $rows,
            'filters' => [
                'buscar' => $request->string('buscar')->toString(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_partida' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:5000',
        ]);

        Partida::create($validated);

        return back();
    }

    public function update(Request $request, int $id)
    {
        $partida = Partida::findOrFail($id);

        $validated = $request->validate([
            'no_partida' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:5000',
        ]);

        $partida->update($validated);

        return back();
    }

    public function destroy(int $id)
    {
        $partida = Partida::findOrFail($id);

        $hasProductos = $this->cx()->table('productos')
            ->where('partida_id', $id)
            ->exists();

        if ($hasProductos) {
            return back()->withErrors(['general' => 'No se puede eliminar: tiene productos asociados.']);
        }

        $partida->delete();

        return back();
    }
}
