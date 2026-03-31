<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partida extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'partidas';

    protected $fillable = ['no_partida', 'descripcion'];

    public function ejercicios(): HasMany
    {
        return $this->hasMany(PartidaPorEjercicio::class, 'partida_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'partida_id');
    }

    public function partidasEspecificas(): HasMany
    {
        return $this->hasMany(PartidaEspecifica::class, 'partida_id');
    }
}
