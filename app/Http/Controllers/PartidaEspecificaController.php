<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PartidaEspecificaController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $ej = $this->ejercicio($request);

        return Inertia::render('PartidasEspecificas/Index', [
            'partidas_especificas' => $this->emptyPage(),
            'partidas' => [],
            'ejercicio' => (int) config('sivso.ejercicio_actual', 2026),
            'anio' => $ej,
            'anios_disponibles' => [$ej],
            'filters' => [
                'buscar' => $request->input('buscar', ''),
                'partida_id' => $request->input('partida_id'),
                'unicas' => $request->input('unicas', '1'),
            ],
        ]);
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo "\xEF\xBB\xBF";
            echo "clave,descripcion\n";
        }, 'lineas-presupuestales.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
