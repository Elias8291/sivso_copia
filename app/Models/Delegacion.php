<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delegacion extends Model
{
    protected $table = 'delegacion';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['codigo', 'ur_referencia'];

    public function dependenciaReferencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'ur_referencia', 'ur');
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'delegacion_codigo', 'codigo');
    }

    public function dependencias(): BelongsToMany
    {
        return $this->belongsToMany(Dependencia::class, 'dependencia_delegacion', 'delegacion_codigo', 'ur', 'codigo', 'ur');
    }

    public function delegados(): BelongsToMany
    {
        return $this->belongsToMany(Delegado::class, 'delegado_delegacion', 'delegacion_codigo', 'delegado_id', 'codigo', 'id');
    }
}
