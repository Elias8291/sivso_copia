<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\PartidaPorEjercicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Vista provisional pública (sin login) para editar líneas presupuestales por ejercicio.
 */
class LineasPresupuestalesProvisionalController extends Controller
{
    public function index(): View
    {
        $anio = (int) config('sivso.ejercicio_actual', 2025);

        $rows = DB::connection('copiasivso')
            ->table('partidas_por_ejercicio as ppe')
            ->join('partidas as p', 'p.id', '=', 'ppe.partida_id')
            ->where('ppe.anio', $anio)
            ->orderBy('p.no_partida')
            ->select([
                'ppe.id as ppe_id',
                'p.id as partida_id',
                'p.no_partida',
                'p.descripcion as partida_descripcion',
                'ppe.anio',
                'ppe.no_partida_snapshot',
                'ppe.clave_como_se_uso',
                'ppe.clave_para_ejercicio',
                'ppe.clave_presupuestal',
            ])
            ->get();

        return view('provisional.lineas-presupuestales', [
            'anio' => $anio,
            'rows' => $rows,
        ]);
    }

    public function update(Request $request, int $ppe): RedirectResponse
    {
        $ppeModel = PartidaPorEjercicio::findOrFail($ppe);

        $merge = [];
        foreach (['no_partida_snapshot', 'clave_presupuestal'] as $key) {
            if ($request->input($key) === '' || $request->input($key) === null) {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $request->merge($merge);
        }

        $validated = $request->validate([
            'no_partida' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:5000',
            'no_partida_snapshot' => 'nullable|integer|min:0',
            'clave_como_se_uso' => 'nullable|string|max:512',
            'clave_para_ejercicio' => 'nullable|string|max:512',
            'clave_presupuestal' => 'nullable|integer|min:0',
        ]);

        $partida = Partida::findOrFail($ppeModel->partida_id);
        $partida->update([
            'no_partida' => $validated['no_partida'],
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        $ppeModel->update([
            'no_partida_snapshot' => $validated['no_partida_snapshot'] ?? null,
            'clave_como_se_uso' => $validated['clave_como_se_uso'] ?? null,
            'clave_para_ejercicio' => $validated['clave_para_ejercicio'] ?? null,
            'clave_presupuestal' => $validated['clave_presupuestal'] ?? null,
        ]);

        return redirect()
            ->route('provisional.lineas-presupuestales')
            ->with('ok', 'Línea actualizada.');
    }
}
