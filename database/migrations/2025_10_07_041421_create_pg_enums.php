<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper para crear un enum de Postgres solo si no existe
        $createEnum = function (string $name, array $values): void {
            $valuesSql = implode(',', array_map(fn($v) => "'" . $v . "'", $values));
            DB::unprepared(<<<SQL
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '{$name}') THEN
                    CREATE TYPE {$name} AS ENUM ({$valuesSql});
                END IF;
            END
            $$;
            SQL);
        };

        // Enums del dominio ARMUTOP_NF
        $createEnum('rol_enum', ['admin','furriel','policia']);
        $createEnum('tipo_item_enum', ['reutilizable','consumible']);
        $createEnum('seguimiento_enum', ['serie','cantidad']);
        $createEnum('tipo_operacion_enum', ['asignacion','devolucion','consumo','mantenimiento_salida','mantenimiento_retorno','ajuste']);
        $createEnum('condicion_enum', ['nuevo','bueno','regular','danado','inoperativo']);
        $createEnum('resultado_inspeccion_enum', ['apto','observado','inoperativo']);
        $createEnum('tipo_mantenimiento_enum', ['preventivo','correctivo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TYPE IF EXISTS tipo_mantenimiento_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS resultado_inspeccion_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS condicion_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS tipo_operacion_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS seguimiento_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS tipo_item_enum CASCADE;");
        DB::unprepared("DROP TYPE IF EXISTS rol_enum CASCADE;");
    }
};
