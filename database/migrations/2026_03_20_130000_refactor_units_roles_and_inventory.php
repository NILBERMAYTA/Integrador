<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->ensureRoleEnum();
            $this->ensureSerieStateEnum();
            $this->addCurrentUnitToUsers();
            $this->renameOperacionDestinationUser();
            $this->normalizeSerieState();
            $this->redesignTransferHistory();
            $this->createUnitInventoryTable();
            $this->seedUnitInventory();
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            Schema::dropIfExists('inventario_unidad_articulos');

            if (Schema::hasTable('user_unidad_asignaciones')) {
                Schema::table('user_unidad_asignaciones', function (Blueprint $table) {
                    if (Schema::hasColumn('user_unidad_asignaciones', 'unidad_origen_id')) {
                        $table->dropConstrainedForeignId('unidad_origen_id');
                    }
                    if (Schema::hasColumn('user_unidad_asignaciones', 'transferido_por')) {
                        $table->dropConstrainedForeignId('transferido_por');
                    }
                    if (Schema::hasColumn('user_unidad_asignaciones', 'fecha_transferencia')) {
                        $table->dropColumn('fecha_transferencia');
                    }
                });

                DB::statement('ALTER TABLE user_unidad_asignaciones RENAME COLUMN unidad_destino_id TO unidad_id');

                Schema::table('user_unidad_asignaciones', function (Blueprint $table) {
                    $table->date('fecha_inicio')->nullable();
                    $table->date('fecha_fin')->nullable();
                });
            }

            if (Schema::hasTable('operaciones') && Schema::hasColumn('operaciones', 'usuario_destino_id')) {
                DB::statement('ALTER TABLE operaciones RENAME COLUMN usuario_destino_id TO policia_id');
            }

            if (Schema::hasTable('users') && Schema::hasColumn('users', 'unidad_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('unidad_id');
                });
            }
        });
    }

    private function ensureRoleEnum(): void
    {
        DB::unprepared(<<<'SQL'
        DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'rol_enum_new') THEN
                CREATE TYPE rol_enum_new AS ENUM ('administrador_general','administrador_unidad','furriel','policia');
            END IF;
        END
        $$;
        SQL);

        DB::unprepared(<<<'SQL'
        ALTER TABLE users
        ALTER COLUMN role DROP DEFAULT;

        ALTER TABLE users
        ALTER COLUMN role TYPE rol_enum_new
        USING (
            CASE role::text
                WHEN 'admin' THEN 'administrador_general'
                WHEN 'furriel' THEN 'furriel'
                WHEN 'policia' THEN 'policia'
                ELSE 'policia'
            END
        )::rol_enum_new;
        SQL);

        DB::unprepared('DROP TYPE IF EXISTS rol_enum CASCADE;');
        DB::unprepared('ALTER TYPE rol_enum_new RENAME TO rol_enum;');
        DB::unprepared("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'policia';");
    }

    private function ensureSerieStateEnum(): void
    {
        DB::unprepared(<<<'SQL'
        DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'estado_serie_enum_new') THEN
                CREATE TYPE estado_serie_enum_new AS ENUM (
                    'disponible',
                    'asignado',
                    'en_mantenimiento',
                    'observado',
                    'inoperativo',
                    'dado_de_baja'
                );
            END IF;
        END
        $$;
        SQL);
    }

    private function addCurrentUnitToUsers(): void
    {
        if (!Schema::hasColumn('users', 'unidad_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('unidad_id')->nullable()->after('role')->constrained('unidades');
            });
        }

        DB::unprepared(<<<'SQL'
        UPDATE users u
        SET unidad_id = COALESCE(
            (
                SELECT uua.unidad_id
                FROM user_unidad_asignaciones uua
                WHERE uua.user_id = u.id AND uua.fecha_fin IS NULL
                ORDER BY uua.fecha_inicio DESC NULLS LAST, uua.id DESC
                LIMIT 1
            ),
            (
                SELECT uua.unidad_id
                FROM user_unidad_asignaciones uua
                WHERE uua.user_id = u.id
                ORDER BY COALESCE(uua.fecha_fin::timestamp, uua.fecha_inicio::timestamp, uua.created_at) DESC NULLS LAST, uua.id DESC
                LIMIT 1
            ),
            (
                SELECT id FROM unidades ORDER BY id ASC LIMIT 1
            )
        )
        WHERE u.unidad_id IS NULL;
        SQL);

        DB::statement('ALTER TABLE users ALTER COLUMN unidad_id SET NOT NULL');
    }

    private function renameOperacionDestinationUser(): void
    {
        if (Schema::hasColumn('operaciones', 'policia_id') && !Schema::hasColumn('operaciones', 'usuario_destino_id')) {
            DB::statement('ALTER TABLE operaciones RENAME COLUMN policia_id TO usuario_destino_id');
        }
    }

    private function normalizeSerieState(): void
    {
        DB::unprepared(<<<'SQL'
        ALTER TABLE articulo_series
        ALTER COLUMN estado DROP DEFAULT;

        ALTER TABLE articulo_series
        ALTER COLUMN estado TYPE estado_serie_enum_new
        USING (
            CASE
                WHEN estado IN ('disponible', 'asignado', 'observado', 'inoperativo', 'dado_de_baja') THEN estado
                WHEN estado = 'en_mantenimiento' THEN 'en_mantenimiento'
                WHEN estado IS NULL OR estado = '' THEN 'disponible'
                ELSE 'disponible'
            END
        )::estado_serie_enum_new;
        SQL);

        DB::unprepared('ALTER TYPE estado_serie_enum_new RENAME TO estado_serie_enum;');
        DB::unprepared("ALTER TABLE articulo_series ALTER COLUMN estado SET DEFAULT 'disponible';");
    }

    private function redesignTransferHistory(): void
    {
        if (Schema::hasColumn('user_unidad_asignaciones', 'unidad_id')) {
            DB::statement('ALTER TABLE user_unidad_asignaciones RENAME COLUMN unidad_id TO unidad_destino_id');
        }

        Schema::table('user_unidad_asignaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('user_unidad_asignaciones', 'unidad_origen_id')) {
                $table->foreignId('unidad_origen_id')->nullable()->after('user_id')->constrained('unidades');
            }
            if (!Schema::hasColumn('user_unidad_asignaciones', 'transferido_por')) {
                $table->foreignId('transferido_por')->nullable()->after('unidad_destino_id')->constrained('users');
            }
            if (!Schema::hasColumn('user_unidad_asignaciones', 'fecha_transferencia')) {
                $table->timestampTz('fecha_transferencia')->nullable()->after('transferido_por');
            }
        });

        DB::unprepared(<<<'SQL'
        WITH ranked AS (
            SELECT
                id,
                LAG(unidad_destino_id) OVER (
                    PARTITION BY user_id
                    ORDER BY COALESCE(fecha_inicio::timestamp, created_at, updated_at), id
                ) AS prev_unidad
            FROM user_unidad_asignaciones
        )
        UPDATE user_unidad_asignaciones uua
        SET unidad_origen_id = ranked.prev_unidad
        FROM ranked
        WHERE ranked.id = uua.id
          AND uua.unidad_origen_id IS NULL;
        SQL);

        DB::unprepared(<<<'SQL'
        UPDATE user_unidad_asignaciones
        SET fecha_transferencia = COALESCE(
            fecha_transferencia,
            created_at,
            fecha_inicio::timestamp,
            updated_at,
            NOW()
        )
        WHERE fecha_transferencia IS NULL;
        SQL);

        DB::unprepared(<<<'SQL'
        UPDATE user_unidad_asignaciones
        SET transferido_por = COALESCE(
            transferido_por,
            (
                SELECT id
                FROM users
                WHERE role::text = 'administrador_general'
                ORDER BY id ASC
                LIMIT 1
            ),
            (
                SELECT id
                FROM users
                ORDER BY id ASC
                LIMIT 1
            )
        )
        WHERE transferido_por IS NULL;
        SQL);

        DB::unprepared(<<<'SQL'
        INSERT INTO user_unidad_asignaciones (
            user_id,
            unidad_origen_id,
            unidad_destino_id,
            transferido_por,
            fecha_transferencia,
            motivo,
            created_at,
            updated_at
        )
        SELECT
            u.id,
            NULL,
            u.unidad_id,
            COALESCE(
                (SELECT id FROM users WHERE role::text = 'administrador_general' ORDER BY id ASC LIMIT 1),
                u.id
            ),
            COALESCE(u.created_at, NOW()),
            'Regularizacion de unidad actual',
            COALESCE(u.created_at, NOW()),
            COALESCE(u.updated_at, NOW())
        FROM users u
        WHERE NOT EXISTS (
            SELECT 1
            FROM user_unidad_asignaciones h
            WHERE h.user_id = u.id
        );
        SQL);

        Schema::table('user_unidad_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('user_unidad_asignaciones', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
            if (Schema::hasColumn('user_unidad_asignaciones', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });

        DB::statement('ALTER TABLE user_unidad_asignaciones ALTER COLUMN unidad_destino_id SET NOT NULL');
        DB::statement('ALTER TABLE user_unidad_asignaciones ALTER COLUMN transferido_por SET NOT NULL');
        DB::statement('ALTER TABLE user_unidad_asignaciones ALTER COLUMN fecha_transferencia SET NOT NULL');
    }

    private function createUnitInventoryTable(): void
    {
        if (Schema::hasTable('inventario_unidad_articulos')) {
            return;
        }

        Schema::create('inventario_unidad_articulos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->decimal('cantidad_disponible', 12, 2)->default(0);
            $table->decimal('cantidad_asignada', 12, 2)->default(0);
            $table->decimal('cantidad_mantenimiento', 12, 2)->default(0);
            $table->timestampsTz();

            $table->unique(['unidad_id', 'articulo_id']);
        });
    }

    private function seedUnitInventory(): void
    {
        DB::unprepared(<<<'SQL'
        INSERT INTO inventario_unidad_articulos (
            unidad_id,
            articulo_id,
            cantidad_disponible,
            cantidad_asignada,
            cantidad_mantenimiento,
            created_at,
            updated_at
        )
        SELECT
            oq.unidad_id,
            oq.articulo_id,
            GREATEST(
                COALESCE(SUM(
                    CASE
                        WHEN oq.tipo = 'ajuste' THEN oq.cantidad
                        WHEN oq.tipo = 'devolucion' THEN oq.cantidad
                        WHEN oq.tipo = 'mantenimiento_retorno' THEN oq.cantidad
                        WHEN oq.tipo = 'asignacion' THEN -oq.cantidad
                        WHEN oq.tipo = 'consumo' THEN -ABS(oq.cantidad)
                        WHEN oq.tipo = 'mantenimiento_salida' THEN -ABS(oq.cantidad)
                        ELSE 0
                    END
                ), 0),
                0
            ) AS cantidad_disponible,
            GREATEST(
                COALESCE(SUM(
                    CASE
                        WHEN oq.tipo = 'asignacion' THEN oq.cantidad
                        WHEN oq.tipo = 'devolucion' THEN -oq.cantidad
                        ELSE 0
                    END
                ), 0),
                0
            ) AS cantidad_asignada,
            GREATEST(
                COALESCE(SUM(
                    CASE
                        WHEN oq.tipo = 'mantenimiento_salida' THEN ABS(oq.cantidad)
                        WHEN oq.tipo = 'mantenimiento_retorno' THEN -ABS(oq.cantidad)
                        ELSE 0
                    END
                ), 0),
                0
            ) AS cantidad_mantenimiento,
            NOW(),
            NOW()
        FROM (
            SELECT
                o.unidad_id,
                od.articulo_id,
                o.tipo,
                od.cantidad
            FROM operacion_detalles od
            INNER JOIN operaciones o ON o.id = od.operacion_id
            INNER JOIN articulos a ON a.id = od.articulo_id
            WHERE a.seguimiento::text = 'cantidad'
              AND od.deleted_at IS NULL
              AND o.deleted_at IS NULL
        ) oq
        GROUP BY oq.unidad_id, oq.articulo_id
        ON CONFLICT (unidad_id, articulo_id) DO UPDATE SET
            cantidad_disponible = EXCLUDED.cantidad_disponible,
            cantidad_asignada = EXCLUDED.cantidad_asignada,
            cantidad_mantenimiento = EXCLUDED.cantidad_mantenimiento,
            updated_at = EXCLUDED.updated_at;
        SQL);
    }
};
