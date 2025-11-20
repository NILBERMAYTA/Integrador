<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->foreignId('operacion_padre_id')
                ->nullable()
                ->constrained('operaciones')
                ->nullOnDelete();
        });

        Schema::table('articulo_series', function (Blueprint $table) {
            $table->string('estado')->default('disponible')->after('codigo_serie');
            $table->foreignId('operacion_detalle_id_actual')
                ->nullable()
                ->after('estado')
                ->constrained('operacion_detalles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articulo_series', function (Blueprint $table) {
            $table->dropForeign(['operacion_detalle_id_actual']);
            $table->dropColumn(['estado', 'operacion_detalle_id_actual']);
        });

        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropForeign(['operacion_padre_id']);
            $table->dropColumn('operacion_padre_id');
        });
    }
};
