<?php

namespace App\Http\Controllers;

use App\Models\Delegacion;
use App\Models\Dependencia;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadosController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'apellido_paterno' => 'nullable|string|max:120',
            'apellido_materno' => 'nullable|string|max:120',
            'nue' => 'nullable|string|max:30',
            'dependencia_id' => ['required', Rule::exists(Dependencia::class, 'id')],
            'delegacion_id' => ['nullable', Rule::exists(Delegacion::class, 'id')],
        ]);

        Empleado::create($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado creado exitosamente');
    }

    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:120',
            'apellido_paterno' => 'nullable|string|max:120',
            'apellido_materno' => 'nullable|string|max:120',
            'nue' => 'nullable|string|max:30',
            'dependencia_id' => ['required', Rule::exists(Dependencia::class, 'id')],
            'delegacion_id' => ['nullable', Rule::exists(Delegacion::class, 'id')],
        ]);

        $empleado->update($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado exitosamente');
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado exitosamente');
    }
}
