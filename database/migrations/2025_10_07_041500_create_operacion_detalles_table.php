<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operacion_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('operacion_id')
              ->constrained('operaciones')   // <-- corregido
              ->cascadeOnDelete();

            $table->foreignId('articulo_id')
              ->constrained('articulos');

            // para seguimiento=cantidad (en serie se sincroniza vía trigger)
            $table->decimal('cantidad', 12, 2)->default(0);

            $table->text('observaciones')->nullable();  // <-- agregado (según DBML)

            $table->timestampsTz();         // <-- corregido
            $table->softDeletesTz();

            $table->index(['operacion_id','articulo_id']);
        });

        DB::unprepared("ALTER TABLE operacion_detalles ADD COLUMN condicion condicion_enum");
    }

    public function down(): void
    {
        Schema::dropIfExists('operacion_detalles');
    }
};
