<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $table = 'empleado';

    public $timestamps = false;

    protected $fillable = [
        'legacy_empleado_id',
        'nue',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'ur',
        'delegacion_codigo',
    ];

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'ur', 'ur');
    }

    public function delegacion(): BelongsTo
    {
        return $this->belongsTo(Delegacion::class, 'delegacion_codigo', 'codigo');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionEmpleadoProducto::class, 'empleado_id');
    }
}
