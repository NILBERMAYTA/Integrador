<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("ALTER TYPE estado_serie_enum ADD VALUE IF NOT EXISTS 'perdido'");
        DB::unprepared("ALTER TYPE estado_serie_enum ADD VALUE IF NOT EXISTS 'robado'");
    }

    public function down(): void
    {
        // PostgreSQL no permite quitar valores de un enum sin recrearlo.
    }
};
