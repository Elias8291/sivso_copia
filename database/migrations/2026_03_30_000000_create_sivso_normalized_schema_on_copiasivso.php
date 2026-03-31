<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'copiasivso';

    protected function createUnlessExists(string $connection, string $table, Closure $definition): void
    {
        if (Schema::connection($connection)->hasTable($table)) {
            return;
        }
        Schema::connection($connection)->create($table, $definition);
    }

    public function up(): void
    {
        $c = $this->connection;

        // ── Catálogos base ──────────────────────────────────

        $this->createUnlessExists($c, 'tipos_partida_especifica', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('codigo')->unique();
            $table->string('nombre', 120)->nullable();
            $table->timestamps();
        });

        $this->createUnlessExists($c, 'dependencias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 12)->nullable()->index();
            $table->unsignedInteger('ur')->nullable()->unique();
            $table->string('nombre', 255);
            $table->string('ur_texto', 120)->nullable();
            $table->timestamps();
        });

        $this->createUnlessExists($c, 'delegaciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 60)->unique();
            $table->string('nombre', 255)->nullable();
            $table->unsignedInteger('ur');
            $table->timestamps();
        });

        // ── Pivots N:M ─────────────────────────────────────

        $this->createUnlessExists($c, 'dependencia_delegacion', function (Blueprint $table) {
            $table->foreignId('dependencia_id')->constrained('dependencias')->cascadeOnDelete();
            $table->foreignId('delegacion_id')->constrained('delegaciones')->cascadeOnDelete();
            $table->primary(['dependencia_id', 'delegacion_id']);
        });

        $this->createUnlessExists($c, 'delegados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 255);
            $table->timestamps();
        });

        $this->createUnlessExists($c, 'delegado_delegacion', function (Blueprint $table) {
            $table->foreignId('delegado_id')->constrained('delegados')->cascadeOnDelete();
            $table->foreignId('delegacion_id')->constrained('delegaciones')->cascadeOnDelete();
            $table->primary(['delegado_id', 'delegacion_id']);
        });

        // ── Partidas ────────────────────────────────────────

        $this->createUnlessExists($c, 'partidas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('no_partida')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        $this->createUnlessExists($c, 'partidas_por_ejercicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedInteger('no_partida_snapshot')->nullable();
            $table->string('clave_como_se_uso', 512)->nullable();
            $table->string('clave_para_ejercicio', 512)->nullable();
            $table->unsignedInteger('clave_presupuestal')->nullable();
            $table->timestamps();
            $table->unique(['partida_id', 'anio']);
            $table->index(['anio', 'no_partida_snapshot']);
        });

        $this->createUnlessExists($c, 'partidas_especificas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->string('clave', 512);
            $table->text('descripcion')->nullable();
            $table->string('clave_partida', 512)->nullable();
            $table->timestamps();
            $table->index(['partida_id', 'anio']);
            $table->index(['anio', 'clave']);
        });

        // ── Productos ───────────────────────────────────────

        $this->createUnlessExists($c, 'productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->foreignId('tipo_partida_especifica_id')
                ->constrained('tipos_partida_especifica')->restrictOnDelete();
            $table->unsignedInteger('lote')->nullable();
            $table->text('descripcion');
            $table->string('marca', 120)->nullable();
            $table->string('unidad_medida', 30)->nullable();
            $table->string('codigo', 60)->nullable()->index();
            $table->string('medida', 10)->nullable();
            $table->enum('origen', ['propuesta', 'concentrado', 'sustitucion'])->default('propuesta');
            $table->unsignedBigInteger('legacy_propuesta_id')->nullable()->unique();
            $table->timestamps();
            $table->index(['partida_id', 'tipo_partida_especifica_id']);
        });

        $this->createUnlessExists($c, 'producto_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 14, 2)->nullable();
            $table->string('proveedor', 120)->nullable();
            $table->timestamps();
            $table->unique(['producto_id', 'anio']);
            $table->index('anio');
        });

        // ── Presupuesto ─────────────────────────────────────

        $this->createUnlessExists($c, 'cupos_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dependencia_id')->constrained('dependencias')->cascadeOnDelete();
            $table->foreignId('partida_id')->constrained('partidas')->cascadeOnDelete();
            $table->foreignId('tipo_partida_especifica_id')
                ->constrained('tipos_partida_especifica')->restrictOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->decimal('cantidad_limite', 15, 2);
            $table->timestamps();
            $table->unique(
                ['dependencia_id', 'partida_id', 'tipo_partida_especifica_id', 'anio'],
                'cupos_pres_dep_part_tipo_anio_uq'
            );
        });

        // ── Empleados ───────────────────────────────────────

        $this->createUnlessExists($c, 'empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nue', 30)->nullable()->index();
            $table->string('nombre', 120)->nullable();
            $table->string('apellido_paterno', 120)->nullable();
            $table->string('apellido_materno', 120)->nullable();
            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->nullOnDelete();
            $table->foreignId('delegacion_id')->nullable()->constrained('delegaciones')->nullOnDelete();
            $table->unsignedInteger('ur')->nullable()->index();
            $table->timestamps();
        });

        // ── Solicitudes (fact table) ────────────────────────

        $this->createUnlessExists($c, 'solicitudes_vestuario', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('producto_original_id')->nullable()
                ->constrained('productos')->nullOnDelete();
            $table->foreignId('partida_especifica_id')->nullable()
                ->constrained('partidas_especificas')->nullOnDelete();

            // FKs denormalizadas para queries rápidas
            $table->foreignId('dependencia_id')->nullable()
                ->constrained('dependencias')->nullOnDelete();
            $table->foreignId('partida_id')->nullable()
                ->constrained('partidas')->nullOnDelete();
            $table->foreignId('tipo_partida_especifica_id')->nullable()
                ->constrained('tipos_partida_especifica')->nullOnDelete();

            // Datos de la solicitud
            $table->unsignedSmallInteger('anio');
            $table->string('talla', 20)->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('importe', 14, 2)->nullable();
            $table->decimal('iva', 12, 2)->nullable();
            $table->decimal('importe_total', 14, 2)->nullable();

            // Control de flujo
            $table->boolean('es_sustitucion')->default(false);
            $table->enum('estado', ['borrador', 'pre_muestreo', 'en_muestreo', 'aprobado'])
                ->default('borrador');

            // Snapshots y legacy
            $table->unsignedInteger('no_partida_snapshot')->nullable();
            $table->string('clave_partida_snapshot', 512)->nullable();
            $table->string('clave_para_ejercicio_snapshot', 512)->nullable();
            $table->unsignedBigInteger('legacy_concentrado_id')->nullable()->unique();
            $table->timestamps();

            // Índices
            $table->index(['empleado_id', 'anio']);
            $table->index(['producto_id', 'anio']);
            $table->index(['anio', 'estado']);
            $table->index(
                ['anio', 'dependencia_id', 'partida_id', 'tipo_partida_especifica_id'],
                'sv_anio_dep_part_tipo_idx'
            );
        });
    }

    public function down(): void
    {
        $c = $this->connection;

        Schema::connection($c)->dropIfExists('solicitudes_vestuario');
        Schema::connection($c)->dropIfExists('empleados');
        Schema::connection($c)->dropIfExists('cupos_presupuesto');
        Schema::connection($c)->dropIfExists('producto_precios');
        Schema::connection($c)->dropIfExists('productos');
        Schema::connection($c)->dropIfExists('partidas_especificas');
        Schema::connection($c)->dropIfExists('partidas_por_ejercicio');
        Schema::connection($c)->dropIfExists('partidas');
        Schema::connection($c)->dropIfExists('delegado_delegacion');
        Schema::connection($c)->dropIfExists('delegados');
        Schema::connection($c)->dropIfExists('dependencia_delegacion');
        Schema::connection($c)->dropIfExists('delegaciones');
        Schema::connection($c)->dropIfExists('dependencias');
        Schema::connection($c)->dropIfExists('tipos_partida_especifica');
    }
};
