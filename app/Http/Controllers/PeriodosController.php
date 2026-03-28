<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodosController extends Controller
{
    public function index(): Response
    {
        $periodos = Periodo::orderBy('año', 'desc')->get()->map(function ($periodo) {
            return [
                'id' => $periodo->id,
                'nombre' => $periodo->nombre,
                'año' => $periodo->año,
                'fecha_inicio' => $periodo->fecha_inicio,
                'fecha_fin' => $periodo->fecha_fin,
                'estado' => $periodo->estado,
                'delegaciones_activas' => 0,
                'empleados_registrados' => 0,
            ];
        });

        return Inertia::render('Periodos/Index', [
            'periodos' => $periodos,
        ]);
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'nombre' => 'required|string|max:100',
            'año' => 'required|integer|min:2000|max:2100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        Periodo::where('estado', 'Abierto')->update(['estado' => 'Cerrado']);

        $validado['estado'] = 'Abierto';

        Periodo::create($validado);

        return redirect()->route('periodos.index');
    }

    public function update(Request $request, Periodo $periodo)
    {
        $validado = $request->validate([
            'nombre' => 'required|string|max:100',
            'año' => 'required|integer|min:2000|max:2100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $periodo->update($validado);

        return redirect()->route('periodos.index');
    }

    public function destroy(Periodo $periodo)
    {
        $periodo->delete();
        return redirect()->route('periodos.index');
    }

    public function cerrar(Periodo $periodo)
    {
        $periodo->update(['estado' => 'Cerrado']);
        return redirect()->route('periodos.index');
    }

    public function reabrir(Periodo $periodo)
    {
        Periodo::where('estado', 'Abierto')->update(['estado' => 'Cerrado']);
        $periodo->update(['estado' => 'Abierto']);
        return redirect()->route('periodos.index');
    }
}
