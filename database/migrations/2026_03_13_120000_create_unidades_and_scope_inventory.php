<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 150)->unique();
            $table->string('sigla', 50)->nullable()->unique();
            $table->string('descripcion', 500)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        $now = now();

        DB::table('unidades')->insert([
            'nombre' => 'UTOP El Alto',
            'sigla' => 'UTOP-EA',
            'descripcion' => 'Unidad inicial del sistema',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $unidadId = (int) DB::table('unidades')->where('sigla', 'UTOP-EA')->value('id');

        Schema::create('user_unidad_asignaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('motivo', 255)->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'fecha_inicio']);
            $table->index(['unidad_id', 'fecha_fin']);
        });

        DB::unprepared(<<<'SQL'
        ALTER TABLE user_unidad_asignaciones
        ADD CONSTRAINT chk_user_unidad_fechas
        CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio);
        SQL);

        DB::unprepared(<<<'SQL'
        CREATE UNIQUE INDEX uq_user_unidad_asignacion_abierta
        ON user_unidad_asignaciones (user_id)
        WHERE fecha_fin IS NULL;
        SQL);

        $usuarios = DB::table('users')->select('id', 'fecha_ingreso')->get();

        foreach ($usuarios as $usuario) {
            DB::table('user_unidad_asignaciones')->insert([
                'user_id' => $usuario->id,
                'unidad_id' => $unidadId,
                'fecha_inicio' => $usuario->fecha_ingreso ?: $now->toDateString(),
                'fecha_fin' => null,
                'motivo' => 'Asignacion inicial del sistema',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('operaciones', function (Blueprint $table) {
            $table->foreignId('unidad_id')
                ->nullable()
                ->after('actor_id')
                ->constrained('unidades');
        });

        DB::table('operaciones')
            ->whereNull('unidad_id')
            ->update(['unidad_id' => $unidadId]);

        DB::statement('ALTER TABLE operaciones ALTER COLUMN unidad_id SET NOT NULL');

        Schema::table('articulo_series', function (Blueprint $table) {
            $table->foreignId('unidad_id')
                ->nullable()
                ->after('articulo_id')
                ->constrained('unidades');
        });

        DB::table('articulo_series')
            ->whereNull('unidad_id')
            ->update(['unidad_id' => $unidadId]);

        DB::statement('ALTER TABLE articulo_series ALTER COLUMN unidad_id SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('articulo_series', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropColumn('unidad_id');
        });

        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropColumn('unidad_id');
        });

        DB::unprepared('DROP INDEX IF EXISTS uq_user_unidad_asignacion_abierta;');

        Schema::dropIfExists('user_unidad_asignaciones');
        Schema::dropIfExists('unidades');
    }
};
