<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Delegado extends Model
{
    protected $table = 'delegado';

    public $timestamps = false;

    protected $fillable = ['nombre_completo', 'nue'];

    public function delegaciones(): BelongsToMany
    {
        return $this->belongsToMany(Delegacion::class, 'delegado_delegacion', 'delegado_id', 'delegacion_codigo', 'id', 'codigo');
    }
}
