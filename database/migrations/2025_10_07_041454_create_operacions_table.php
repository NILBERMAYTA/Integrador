<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operaciones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('evento_id')->nullable()->constrained('eventos'); // NULL en ajustes/mto
            $table->foreignId('policia_id')->nullable()->constrained('users');  // receptor/afectado
            $table->foreignId('actor_id')->constrained('users');                 // quien registra

            $table->timestampTz('fecha');                       // <-- campo correcto
            $table->text('observaciones')->nullable();

            $table->timestampsTz();                             // created_at/updated_at (con TZ)
            $table->softDeletesTz();                            // deleted_at (con TZ)

            $table->index(['policia_id','fecha']);
            $table->index('fecha');
        });

        // Enum nativo (asegúrate de que create_pg_enums ya corrió)
        DB::unprepared("ALTER TABLE operaciones ADD COLUMN tipo tipo_operacion_enum NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones');
    }
};
