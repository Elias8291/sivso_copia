<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoCotizado extends Model
{
    protected $table = 'producto_cotizado';

    public $timestamps = false;

    protected $fillable = [
        'anio',
        'producto_licitado_id',
        'numero_partida',
        'partida_especifica',
        'clave',
        'descripcion',
        'precio_unitario',
        'importe',
        'iva',
        'total',
        'precio_alterno',
        'referencia_codigo',
        'clasificacion_principal_id',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario' => 'decimal:2',
            'importe' => 'decimal:2',
            'iva' => 'decimal:2',
            'total' => 'decimal:2',
            'precio_alterno' => 'decimal:2',
        ];
    }

    public function productoLicitado(): BelongsTo
    {
        return $this->belongsTo(ProductoLicitado::class, 'producto_licitado_id');
    }

    public function clasificacionPrincipal(): BelongsTo
    {
        return $this->belongsTo(ClasificacionBien::class, 'clasificacion_principal_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionEmpleadoProducto::class, 'producto_cotizado_id');
    }

    public function clasificaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            ClasificacionBien::class,
            'producto_cotizado_clasificacion',
            'producto_cotizado_id',
            'clasificacion_id'
        );
    }
}
