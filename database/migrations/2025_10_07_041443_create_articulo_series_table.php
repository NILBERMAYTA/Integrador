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
        Schema::create('articulo_series', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('articulo_id')
                ->constrained("articulos")
                ->cascadeOnDelete();
            $table->string('codigo_serie', 100)->unique();
            $table->string('observaciones', 500)->nullable();

            $table->timestamps();
            $table->softDeletesTz();

            $table->index('articulo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_series');
    }
};
