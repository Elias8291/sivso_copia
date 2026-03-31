<?php

namespace App\Http\Controllers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MyWardrobeController extends Controller
{
    private function cx(): Connection
    {
        return DB::connection('copiasivso');
    }

    public function index(): Response
    {
        $user = auth()->user();
        $ejercicio = (int) config('sivso.ejercicio_actual', 2025);

        // Obtener empleado del usuario - buscar por nue
        $empleado = $this->cx()->table('empleados')
            ->whereNotNull('nue')
            ->where('nue', trim($user->nue ?? ''))
            ->first();

        if (! $empleado) {
            return Inertia::render('MyWardrobe/Index', [
                'employee' => null,
                'wardrobeItems' => [],
                'message' => 'No se encontró registro de empleado para este usuario.',
            ]);
        }

        // Obtener dependencia del empleado
        $dependencia = $this->cx()->table('dependencias')
            ->where('id', $empleado->dependencia_id)
            ->first();

        $employee = [
            'name' => $user->name,
            'department' => $dependencia?->nombre ?? 'Sin departamento',
            'position' => $empleado->puesto ?? 'Sin puesto',
            'status' => 'Pendiente de confirmación',
            'deadline' => '15 de Abril, 2026',
        ];

        // Obtener solicitudes de vestuario para el ejercicio actual
        $solicitudes = $this->cx()->table('solicitudes_vestuario as sv')
            ->join('productos as p', 'p.id', '=', 'sv.producto_id')
            ->leftJoin('partidas_especificas as pe', 'pe.id', '=', 'sv.partida_especifica_id')
            ->leftJoin('partidas as pa', 'pa.id', '=', 'pe.partida_id')
            ->where('sv.empleado_id', $empleado->id)
            ->where('sv.anio', $ejercicio)
            ->select([
                'sv.id',
                'sv.producto_id',
                'sv.partida_especifica_id',
                'sv.talla',
                'sv.cantidad',
                'sv.precio_unitario',
                'sv.importe_total',
                'p.descripcion as producto_descripcion',
                'p.marca',
                'p.medida',
                'p.unidad_medida',
                'pe.clave as partida_especifica_clave',
                'pe.descripcion as partida_especifica_descripcion',
                'pa.no_partida',
            ])
            ->orderBy('pa.no_partida')
            ->orderBy('pe.clave')
            ->orderBy('p.descripcion')
            ->get();

        $wardrobeItems = $solicitudes->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->producto_id,
                'partida_especifica_id' => $item->partida_especifica_id,
                'name' => $item->producto_descripcion,
                'type' => $item->partida_especifica_descripcion ?? 'Sin clasificar',
                'description' => $item->marca ? "Marca: {$item->marca}" : '',
                'size' => $item->talla,
                'quantity' => $item->cantidad,
                'price' => $item->precio_unitario,
                'total' => $item->importe_total,
                'unit' => $item->unidad_medida,
                'partida_especifica_clave' => $item->partida_especifica_clave,
                'no_partida' => $item->no_partida,
            ];
        })->values();

        return Inertia::render('MyWardrobe/Index', [
            'employee' => $employee,
            'wardrobeItems' => $wardrobeItems,
            'ejercicio' => $ejercicio,
            'totalItems' => $solicitudes->count(),
        ]);
    }
}
