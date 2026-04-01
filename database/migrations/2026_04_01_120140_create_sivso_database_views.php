<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW v_licitacion_vigente AS
SELECT pl.*
FROM producto_licitado pl
INNER JOIN configuracion_sivso cfg
  ON cfg.clave = 'anio_catalogo_activo'
 AND pl.anio = CAST(cfg.valor AS UNSIGNED)
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW v_cotizacion_oficial_vigente AS
SELECT pc.*
FROM producto_cotizado pc
INNER JOIN configuracion_sivso cfg
  ON cfg.clave = 'anio_catalogo_activo'
 AND pc.anio = CAST(cfg.valor AS UNSIGNED)
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW v_cotizacion_oficial_con_licitacion AS
SELECT
  pc.id AS cotizado_id,
  pc.referencia_codigo,
  pc.clave AS clave_cotizada,
  pc.descripcion AS descripcion_cotizada,
  pc.precio_unitario,
  pc.precio_alterno,
  pl.id AS licitado_id,
  pl.numero_partida,
  pl.partida_especifica,
  pl.codigo_catalogo,
  pl.descripcion AS descripcion_licitacion,
  pl.precio_unitario AS precio_licitacion_unitario,
  pl.anio
FROM producto_cotizado pc
INNER JOIN producto_licitado pl ON pl.id = pc.producto_licitado_id
INNER JOIN configuracion_sivso cfg
  ON cfg.clave = 'anio_catalogo_activo'
 AND pc.anio = CAST(cfg.valor AS UNSIGNED)
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP VIEW IF EXISTS v_cotizacion_oficial_con_licitacion');
        DB::unprepared('DROP VIEW IF EXISTS v_cotizacion_oficial_vigente');
        DB::unprepared('DROP VIEW IF EXISTS v_licitacion_vigente');
    }
};
