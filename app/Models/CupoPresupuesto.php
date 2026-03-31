<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CupoPresupuesto extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'cupos_presupuesto';

    protected $fillable = [
        'dependencia_id',
        'partida_id',
        'tipo_partida_especifica_id',
        'anio',
        'cantidad_limite',
    ];

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function tipoPartidaEspecifica(): BelongsTo
    {
        return $this->belongsTo(TipoPartidaEspecifica::class, 'tipo_partida_especifica_id');
    }
}
