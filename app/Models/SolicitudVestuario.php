<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudVestuario extends Model
{
    protected $connection = 'copiasivso';

    protected $table = 'solicitudes_vestuario';

    protected $fillable = [
        'empleado_id',
        'producto_id',
        'producto_original_id',
        'partida_especifica_id',
        'dependencia_id',
        'partida_id',
        'tipo_partida_especifica_id',
        'anio',
        'talla',
        'cantidad',
        'precio_unitario',
        'importe',
        'iva',
        'importe_total',
        'es_sustitucion',
        'estado',
        'no_partida_snapshot',
        'clave_partida_snapshot',
        'clave_para_ejercicio_snapshot',
        'legacy_concentrado_id',
    ];

    protected $casts = [
        'es_sustitucion' => 'boolean',
    ];

    private const TRANSICIONES = [
        'borrador' => ['pre_muestreo'],
        'pre_muestreo' => ['en_muestreo', 'borrador'],
        'en_muestreo' => ['aprobado', 'pre_muestreo'],
        'aprobado' => ['en_muestreo'],
    ];

    // ── Relaciones ──────────────────────────────────────

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function productoOriginal(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_original_id');
    }

    public function partidaEspecifica(): BelongsTo
    {
        return $this->belongsTo(PartidaEspecifica::class, 'partida_especifica_id');
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function tipoPartidaEspecifica(): BelongsTo
    {
        return $this->belongsTo(TipoPartidaEspecifica::class, 'tipo_partida_especifica_id');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeBorrador(Builder $q): Builder
    {
        return $q->where('estado', 'borrador');
    }

    public function scopePreMuestreo(Builder $q): Builder
    {
        return $q->where('estado', 'pre_muestreo');
    }

    public function scopeEnMuestreo(Builder $q): Builder
    {
        return $q->where('estado', 'en_muestreo');
    }

    public function scopeAprobado(Builder $q): Builder
    {
        return $q->where('estado', 'aprobado');
    }

    // ── Lógica de negocio ───────────────────────────────

    public function puedeTransicionarA(string $nuevoEstado): bool
    {
        return in_array($nuevoEstado, self::TRANSICIONES[$this->estado] ?? []);
    }

    public function transicionarA(string $nuevoEstado): void
    {
        if (! $this->puedeTransicionarA($nuevoEstado)) {
            throw new \LogicException(
                "No se puede pasar de [{$this->estado}] a [{$nuevoEstado}]"
            );
        }

        $this->estado = $nuevoEstado;
    }

    /**
     * Sustituir el producto actual por uno nuevo (solo en estado en_muestreo).
     * Guarda el producto anterior como producto_original si es la primera sustitución.
     */
    public function sustituirProducto(Producto $nuevoProducto): void
    {
        if ($this->estado !== 'en_muestreo') {
            throw new \LogicException('Solo se puede sustituir en estado en_muestreo');
        }

        $this->producto_original_id ??= $this->producto_id;
        $this->producto_id = $nuevoProducto->id;
        $this->es_sustitucion = true;

        $precio = $nuevoProducto->precios()
            ->where('anio', $this->anio)->first();

        if ($precio) {
            $this->precio_unitario = $precio->precio_unitario;
            $this->importe = $precio->precio_unitario * $this->cantidad;
            $this->iva = round($this->importe * 0.16, 2);
            $this->importe_total = $this->importe + $this->iva;
        }

        $this->syncDenormFks();
        $this->save();
    }

    public function syncDenormFks(): void
    {
        $this->dependencia_id = $this->empleado?->dependencia_id;
        $this->partida_id = $this->producto?->partida_id;
        $this->tipo_partida_especifica_id = $this->producto?->tipo_partida_especifica_id;
    }
}
