<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->decimal('latitud', 10, 7)->nullable()->after('descripcion');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
        });

        Schema::table('articulo_series', function (Blueprint $table) {
            $table->string('foto_path')->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });

        Schema::table('articulo_series', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }
};
