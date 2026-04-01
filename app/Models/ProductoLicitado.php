<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoLicitado extends Model
{
    protected $table = 'producto_licitado';

    public $timestamps = false;

    protected $fillable = [
        'anio',
        'numero_partida',
        'lote',
        'partida_especifica',
        'codigo_catalogo',
        'descripcion',
        'cantidad_propuesta',
        'unidad',
        'marca',
        'precio_unitario',
        'subtotal',
        'proveedor',
        'medida',
        'clasificacion_principal_id',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function clasificacionPrincipal(): BelongsTo
    {
        return $this->belongsTo(ClasificacionBien::class, 'clasificacion_principal_id');
    }

    public function productosCotizados(): HasMany
    {
        return $this->hasMany(ProductoCotizado::class, 'producto_licitado_id');
    }

    public function clasificaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ClasificacionBien::class,
            'producto_licitado_clasificacion',
            'producto_licitado_id',
            'clasificacion_id'
        );
    }
}
