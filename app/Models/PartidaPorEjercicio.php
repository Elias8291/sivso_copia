<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidaPorEjercicio extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'partidas_por_ejercicio';

    protected $fillable = [
        'partida_id',
        'anio',
        'no_partida_snapshot',
        'clave_como_se_uso',
        'clave_para_ejercicio',
        'clave_presupuestal',
    ];

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }
}
