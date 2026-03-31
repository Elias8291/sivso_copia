<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'empleados';

    protected $fillable = [
        'nue',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'dependencia_id',
        'delegacion_id',
        'ur',
    ];

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function delegacion(): BelongsTo
    {
        return $this->belongsTo(Delegacion::class, 'delegacion_id');
    }

    public function solicitudesVestuario(): HasMany
    {
        return $this->hasMany(SolicitudVestuario::class, 'empleado_id');
    }
}
