<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PeriodoController extends Controller
{
    public function index(): Response
    {
        $periodos = Periodo::query()
            ->orderByDesc('anio')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Periodo $p) => $this->toFrontend($p));

        return Inertia::render('Periodos/Index', [
            'periodos' => $periodos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Periodo::query()->create([
            'nombre' => $validated['nombre'],
            'anio' => (int) $validated['año'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => Periodo::ESTADO_ABIERTO,
        ]);

        $this->ensureSingleAbierto();

        return redirect()->route('periodos.index');
    }

    public function update(Request $request, Periodo $periodo): RedirectResponse
    {
        $validated = $this->validated($request);

        $periodo->update([
            'nombre' => $validated['nombre'],
            'anio' => (int) $validated['año'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
        ]);

        return redirect()->route('periodos.index');
    }

    public function destroy(Periodo $periodo): RedirectResponse
    {
        $periodo->delete();

        return redirect()->route('periodos.index');
    }

    public function cerrar(Periodo $periodo): RedirectResponse
    {
        $periodo->update(['estado' => Periodo::ESTADO_CERRADO]);

        return redirect()->route('periodos.index');
    }

    public function reabrir(Periodo $periodo): RedirectResponse
    {
        Periodo::query()->where('estado', Periodo::ESTADO_ABIERTO)->update(['estado' => Periodo::ESTADO_CERRADO]);
        $periodo->update(['estado' => Periodo::ESTADO_ABIERTO]);

        return redirect()->route('periodos.index');
    }

    /**
     * @return array{nombre: string, año: int|string, fecha_inicio: string, fecha_fin: string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'año' => ['required', 'integer', 'min:1990', 'max:2100'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
        ]);
    }

    private function ensureSingleAbierto(): void
    {
        $abiertos = Periodo::query()->where('estado', Periodo::ESTADO_ABIERTO)->orderByDesc('id')->get();
        if ($abiertos->count() <= 1) {
            return;
        }
        foreach ($abiertos->slice(1) as $p) {
            $p->update(['estado' => Periodo::ESTADO_CERRADO]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toFrontend(Periodo $p): array
    {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'año' => $p->anio,
            'fecha_inicio' => $p->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $p->fecha_fin->format('Y-m-d'),
            'estado' => $p->estado === Periodo::ESTADO_ABIERTO ? 'Abierto' : 'Cerrado',
        ];
    }
}
