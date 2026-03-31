<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'productos';

    protected $fillable = [
        'partida_id',
        'tipo_partida_especifica_id',
        'lote',
        'descripcion',
        'marca',
        'unidad_medida',
        'codigo',
        'medida',
        'origen',
        'legacy_propuesta_id',
    ];

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function tipoPartidaEspecifica(): BelongsTo
    {
        return $this->belongsTo(TipoPartidaEspecifica::class, 'tipo_partida_especifica_id');
    }

    public function precios(): HasMany
    {
        return $this->hasMany(ProductoPrecio::class, 'producto_id');
    }
}
