<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('unidad_id')
                ->constrained('unidades')
                ->restrictOnDelete();
            $table->string('nombre', 150);
            $table->string('descripcion', 500)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['unidad_id', 'nombre']);
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
