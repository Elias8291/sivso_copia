<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PartidaController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        return Inertia::render('Partidas/Index', [
            'partidas' => [],
            'filters' => [
                'buscar' => $request->input('buscar', ''),
            ],
        ]);
    }
}
