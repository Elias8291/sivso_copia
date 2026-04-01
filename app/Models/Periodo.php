<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'periodo';

    public const ESTADO_ABIERTO = 'abierto';

    public const ESTADO_CERRADO = 'cerrado';

    protected $fillable = [
        'nombre',
        'anio',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function isAbierto(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO;
    }
}
