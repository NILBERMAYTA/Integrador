<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operacion_detalle_series', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('operacion_detalle_id')
                ->constrained("operacion_detalles")
                ->cascadeOnDelete();
            $table->foreignId('serie_id')
                ->constrained("articulo_series");
            
            $table->timestamps();
            $table->softDeletesTz();

            $table->unique(['operacion_detalle_id', 'serie_id']);
            $table->index('serie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operacion_detalle_series');
    }
};
