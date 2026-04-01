<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSivso extends Model
{
    protected $table = 'configuracion_sivso';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['clave', 'valor'];
}
