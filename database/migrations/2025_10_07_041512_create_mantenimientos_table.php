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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('articulo_id')
                ->constrained("articulos");
            $table->foreignId('serie_id')
                ->nullable()
                ->constrained("articulo_series");
            $table->foreignId('created_por')
                ->constrained("users");
            $table->dateTimeTz('fecha_inicio')->nullable();
            $table->dateTimeTz('fecha_fin')->nullable();
            //$table->string('costo', 500)->nullable();

            $table->timestamps();
            $table->softDeletesTz();

            $table->index(['articulo_id','serie_id']);
            $table->index('fecha_inicio');
        });

        DB::unprepared("ALTER TABLE mantenimientos ADD COLUMN tipo tipo_mantenimiento_enum NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
