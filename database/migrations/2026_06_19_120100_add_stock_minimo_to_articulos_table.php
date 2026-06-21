<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            // Stock minimo a nivel de articulo (referencia general para semaforo
            // de stock). El stock por unidad sigue viviendo en
            // inventario_unidad_articulos.
            $table->decimal('stock_minimo', 12, 2)->default(0)->after('unidad_medida');
        });
    }

    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('stock_minimo');
        });
    }
};
