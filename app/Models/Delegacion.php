<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delegacion extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'delegaciones';

    protected $fillable = ['clave', 'nombre', 'ur'];

    public function dependencias(): BelongsToMany
    {
        return $this->belongsToMany(Dependencia::class, 'dependencia_delegacion');
    }

    public function delegados(): BelongsToMany
    {
        return $this->belongsToMany(Delegado::class, 'delegado_delegacion');
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }
}
