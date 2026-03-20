<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'condicion_fisica_serie_enum') THEN
                CREATE TYPE condicion_fisica_serie_enum AS ENUM (
                    'bueno',
                    'con_defectos',
                    'malo',
                    'inoperativo'
                );
            END IF;
        END
        $$;
        SQL);

        Schema::table('articulo_series', function (Blueprint $table) {
            $table->string('condicion_actual')->nullable()->after('estado');
        });

        DB::unprepared(<<<'SQL'
        UPDATE articulo_series
        SET condicion_actual = CASE
            WHEN estado::text = 'inoperativo' THEN 'inoperativo'
            WHEN estado::text = 'observado' THEN 'con_defectos'
            ELSE 'bueno'
        END
        WHERE condicion_actual IS NULL;
        SQL);

        DB::unprepared(<<<'SQL'
        ALTER TABLE articulo_series
        ALTER COLUMN condicion_actual TYPE condicion_fisica_serie_enum
        USING condicion_actual::condicion_fisica_serie_enum;
        SQL);

        DB::unprepared("ALTER TABLE articulo_series ALTER COLUMN condicion_actual SET DEFAULT 'bueno';");
        DB::statement('ALTER TABLE articulo_series ALTER COLUMN condicion_actual SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('articulo_series', function (Blueprint $table) {
            $table->dropColumn('condicion_actual');
        });

        DB::unprepared('DROP TYPE IF EXISTS condicion_fisica_serie_enum CASCADE;');
    }
};
