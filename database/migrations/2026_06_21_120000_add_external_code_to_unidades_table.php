<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            // Columna DES del archivo de migracion de personal.
            $table->string('codigo_externo', 20)
                ->nullable()
                ->unique()
                ->after('sigla');
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropUnique(['codigo_externo']);
            $table->dropColumn('codigo_externo');
        });
    }
};
