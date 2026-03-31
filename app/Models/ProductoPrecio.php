<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoPrecio extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'producto_precios';

    protected $fillable = ['producto_id', 'anio', 'precio_unitario', 'subtotal', 'proveedor'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
