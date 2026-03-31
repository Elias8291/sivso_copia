<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\SolicitudVestuario;
use Illuminate\Support\Facades\DB;

class ClonarEjercicioService
{
    /**
     * Clona las solicitudes aprobadas del año anterior al nuevo año para una delegación.
     *
     * @return array{creadas: int, sin_producto: list<array>, total_origen: int}
     */
    public function clonar(int $anioOrigen, int $anioDestino, int $delegacionId): array
    {
        $solicitudesOrigen = SolicitudVestuario::where('anio', $anioOrigen)
            ->whereHas('empleado', fn ($q) => $q->where('delegacion_id', $delegacionId))
            ->where('estado', 'aprobado')
            ->with(['empleado', 'producto'])
            ->get();

        $creadas = 0;
        $sinProducto = [];

        DB::connection('copiasivso')->transaction(function () use (
            $solicitudesOrigen, $anioDestino, &$creadas, &$sinProducto
        ) {
            foreach ($solicitudesOrigen as $sol) {
                $productoNuevo = Producto::where('codigo', $sol->producto->codigo)
                    ->whereHas('precios', fn ($q) => $q->where('anio', $anioDestino))
                    ->first();

                if (! $productoNuevo) {
                    $sinProducto[] = [
                        'empleado_id' => $sol->empleado_id,
                        'empleado' => trim($sol->empleado->nombre.' '.$sol->empleado->apellido_paterno),
                        'producto' => $sol->producto->descripcion,
                        'codigo' => $sol->producto->codigo,
                    ];

                    continue;
                }

                $precio = $productoNuevo->precios()->where('anio', $anioDestino)->first();
                $precioUnit = $precio?->precio_unitario;
                $importe = $precioUnit ? $precioUnit * $sol->cantidad : null;
                $iva = $importe ? round($importe * 0.16, 2) : null;

                $nueva = SolicitudVestuario::create([
                    'empleado_id' => $sol->empleado_id,
                    'producto_id' => $productoNuevo->id,
                    'anio' => $anioDestino,
                    'talla' => $sol->talla,
                    'cantidad' => $sol->cantidad,
                    'precio_unitario' => $precioUnit,
                    'importe' => $importe,
                    'iva' => $iva,
                    'importe_total' => $importe && $iva ? $importe + $iva : null,
                    'estado' => 'borrador',
                ]);

                $nueva->syncDenormFks();
                $nueva->save();

                $creadas++;
            }
        });

        return [
            'creadas' => $creadas,
            'sin_producto' => $sinProducto,
            'total_origen' => $solicitudesOrigen->count(),
        ];
    }
}
