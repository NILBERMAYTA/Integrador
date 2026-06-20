<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_unidad_articulos', function (Blueprint $table) {
            $table->decimal('stock_minimo', 12, 2)->default(0)->after('cantidad_mantenimiento');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_unidad_articulos', function (Blueprint $table) {
            $table->dropColumn('stock_minimo');
        });
    }
};
