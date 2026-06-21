<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('nivel', 20)->default('medio')->after('descripcion');
            $table->string('estado', 20)->default('planificado')->after('nivel');
        });

        DB::statement("ALTER TABLE eventos ADD CONSTRAINT chk_eventos_nivel CHECK (nivel IN ('bajo','medio','alto'))");
        DB::statement("ALTER TABLE eventos ADD CONSTRAINT chk_eventos_estado CHECK (estado IN ('planificado','activo','cerrado'))");

        Schema::table('eventos', function (Blueprint $table) {
            $table->index('nivel');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE eventos DROP CONSTRAINT IF EXISTS chk_eventos_nivel');
        DB::statement('ALTER TABLE eventos DROP CONSTRAINT IF EXISTS chk_eventos_estado');

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex(['nivel']);
            $table->dropIndex(['estado']);
            $table->dropColumn(['nivel', 'estado']);
        });
    }
};
