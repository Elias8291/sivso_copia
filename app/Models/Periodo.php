<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $fillable = [
        'nombre',
        'año',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];
}
