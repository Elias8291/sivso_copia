<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartidaEspecifica extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'partidas_especificas';

    protected $fillable = [
        'partida_id',
        'anio',
        'clave',
        'descripcion',
        'clave_partida',
    ];

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudVestuario::class, 'partida_especifica_id');
    }
}
