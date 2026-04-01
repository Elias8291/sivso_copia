<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionEmpleadoProducto extends Model
{
    protected $table = 'asignacion_empleado_producto';

    public $timestamps = false;

    protected $fillable = [
        'anio',
        'empleado_id',
        'producto_licitado_id',
        'producto_cotizado_id',
        'clave_partida_presupuestal',
        'cantidad',
        'talla',
        'cantidad_secundaria',
        'clave_presupuestal',
        'legacy_concentrado_id',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function productoLicitado(): BelongsTo
    {
        return $this->belongsTo(ProductoLicitado::class, 'producto_licitado_id');
    }

    public function productoCotizado(): BelongsTo
    {
        return $this->belongsTo(ProductoCotizado::class, 'producto_cotizado_id');
    }
}
