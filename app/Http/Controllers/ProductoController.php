<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductoController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $ej = (int) config('sivso.ejercicio_actual', 2026);
        $anio = (int) $request->input('anio', $ej);

        return Inertia::render('Productos/Index', [
            'productos' => $this->emptyPage(),
            'partidas' => [],
            'tipos' => [],
            'ejercicio' => $ej,
            'anio' => $anio,
            'filters' => [
                'buscar' => $request->input('buscar', ''),
                'partida_id' => $request->input('partida_id'),
            ],
        ]);
    }
}
