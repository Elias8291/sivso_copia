<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dependencia extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'dependencias';

    protected $fillable = ['codigo', 'ur', 'nombre', 'ur_texto'];

    public function delegaciones(): BelongsToMany
    {
        return $this->belongsToMany(Delegacion::class, 'dependencia_delegacion');
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }

    public function cuposPresupuesto(): HasMany
    {
        return $this->hasMany(CupoPresupuesto::class);
    }
}
