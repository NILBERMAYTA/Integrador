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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tipo_id')
                ->constrained("tipos_incidente");
            $table->foreignId('articulo_id')
                ->constrained("articulos");
            $table->foreignId('serie_id')
                ->constrained("articulo_series");
            $table->foreignId('policia_id')
                ->constrained("users");
            $table->text('descripcion')->nullable();
            $table->dateTimeTz('fecha');
            $table->foreignId('created_por')
                ->constrained("users");

            $table->timestamps();
            $table->softDeletesTz();

            $table->index(['articulo_id','serie_id']);
            $table->index(['policia_id','fecha']);
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
