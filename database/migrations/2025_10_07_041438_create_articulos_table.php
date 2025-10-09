<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articulos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('categoria_id')->constrained()->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('unidad_medida', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletesTz();

            $table->unique(['categoria_id','nombre']);
        });

        // Campos ENUM nativos (PostgreSQL) – requieren que create_pg_enums ya haya corrido
        DB::unprepared("ALTER TABLE articulos ADD COLUMN tipo tipo_item_enum NOT NULL");
        DB::unprepared("ALTER TABLE articulos ADD COLUMN seguimiento seguimiento_enum NOT NULL");

        // (Opcional recomendado) CHECK de coherencia: si hay serie ⇒ debe ser reutilizable
        DB::unprepared("
            ALTER TABLE articulos
            ADD CONSTRAINT chk_articulo_seg_tipo
            CHECK (
                (seguimiento = 'serie' AND tipo = 'reutilizable')
                OR (seguimiento = 'cantidad')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
