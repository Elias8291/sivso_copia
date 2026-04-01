<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clave primaria compuesta; usar consultas explícitas donde haga falta la PK.
 */
class CupoDependenciaPartida extends Model
{
    protected $table = 'cupo_dependencia_partida';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'monto_limite' => 'decimal:2',
        ];
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'ur', 'ur');
    }
}
