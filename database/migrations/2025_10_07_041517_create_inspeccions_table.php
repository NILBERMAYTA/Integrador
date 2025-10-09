<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspecciones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('serie_id')->nullable()->constrained('articulo_series');
            $table->foreignId('inspector_id')->constrained('users');

            $table->text('observaciones')->nullable();
            $table->timestampTz('realizada_en')->nullable();   // <- corregido

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['articulo_id','serie_id']);
            $table->index('realizada_en');
        });

        // requiere que la migración create_pg_enums ya se haya ejecutado
        DB::unprepared("ALTER TABLE inspecciones ADD COLUMN resultado resultado_inspeccion_enum NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('inspecciones'); // <- nombre correcto
    }
};
