<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cedula', 20)
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('nivel', 30)->nullable()->after('apellido_materno');

            // GRADO se almacena en el campo rango que ya existe.
            // Estos son los codigos RAN y GRA00 del sistema origen.
            $table->string('rango_codigo', 10)->nullable()->after('rango');
            $table->string('grado_codigo', 10)->nullable()->after('rango_codigo');

            $table->string('cargo', 500)->nullable()->after('grado_codigo');

            $table->foreignId('destino_id')
                ->nullable()
                ->after('unidad_id')
                ->constrained('destinos')
                ->nullOnDelete();

            // Las dos columnas POST GRADO son codigos independientes.
            $table->string('post_grado_codigo_1', 10)->nullable()->after('destino_id');
            $table->string('categoria_codigo', 10)->nullable()->after('post_grado_codigo_1');
            $table->string('post_grado_codigo_2', 10)->nullable()->after('categoria_codigo');

            $table->date('fecha_nacimiento')->nullable()->after('fecha_ingreso');
            $table->string('marca', 100)->nullable()->after('fecha_nacimiento');
            $table->string('expedido', 10)->nullable()->after('marca');
            $table->string('sexo', 10)->nullable()->after('expedido');
            $table->string('promocion', 20)->nullable()->after('sexo');
            $table->string('celular', 30)->nullable()->after('numero_escalafon');
            $table->string('sigep', 50)->nullable()->after('celular');
            $table->string('salida_haberes_codigo', 20)->nullable()->after('sigep');

            $table->index(['nivel', 'rango']);
            $table->index('rango_codigo');
            $table->index('categoria_codigo');
            $table->index('promocion');
            $table->index('numero_escalafon');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['destino_id']);

            $table->dropIndex(['nivel', 'rango']);
            $table->dropIndex(['rango_codigo']);
            $table->dropIndex(['categoria_codigo']);
            $table->dropIndex(['promocion']);
            $table->dropIndex(['numero_escalafon']);
            $table->dropUnique(['cedula']);

            $table->dropColumn([
                'cedula',
                'nivel',
                'rango_codigo',
                'grado_codigo',
                'cargo',
                'destino_id',
                'post_grado_codigo_1',
                'categoria_codigo',
                'post_grado_codigo_2',
                'fecha_nacimiento',
                'marca',
                'expedido',
                'sexo',
                'promocion',
                'celular',
                'sigep',
                'salida_haberes_codigo',
            ]);
        });
    }
};
