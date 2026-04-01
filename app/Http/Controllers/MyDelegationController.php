<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StubsInertiaMutations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class MyDelegationController extends Controller
{
    use StubsInertiaMutations;

    public function index(Request $request): Response
    {
        $ej = $this->ejercicio($request);

        return Inertia::render('MyDelegation/Index', [
            'employees' => [],
            'delegation_name' => '—',
            'delegaciones' => [],
            'delegacion_activa_id' => null,
            'ejercicio' => $ej,
            'bajas' => ['total' => 0, 'importe' => 0],
            'dependencias' => [],
            'delegaciones_por_ur' => (object) [],
        ]);
    }

    public function show(string $id): Response
    {
        return Inertia::render('MyDelegation/Show', [
            'employee' => [
                'id' => $id,
                'name' => '—',
                'nue' => '—',
                'dependencia' => '—',
                'delegacion' => '—',
            ],
            'wardrobeItems' => [],
        ]);
    }
}
