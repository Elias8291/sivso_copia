<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dependencia extends Model
{
    protected $table = 'dependencia';

    protected $primaryKey = 'ur';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['ur', 'nombre', 'nombre_corto'];

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'ur', 'ur');
    }

    public function delegaciones(): BelongsToMany
    {
        return $this->belongsToMany(Delegacion::class, 'dependencia_delegacion', 'ur', 'delegacion_codigo', 'ur', 'codigo');
    }
}
