<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPartidaEspecifica extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'tipos_partida_especifica';

    protected $fillable = ['codigo', 'nombre'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'tipo_partida_especifica_id');
    }
}
