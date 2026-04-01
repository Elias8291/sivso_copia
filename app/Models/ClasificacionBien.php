<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasificacionBien extends Model
{
    protected $table = 'clasificacion_bien';

    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre'];
}
