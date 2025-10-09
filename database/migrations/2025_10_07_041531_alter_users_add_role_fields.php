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
        // Asegúrate de que la migración default de users ya corrió
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('can_login')->default(false)->after('password');
            $t->string('rango', 50)->nullable()->after('can_login');
            $t->string('numero_escalafon', 50)->nullable()->after('rango');
            $t->date('fecha_ingreso')->nullable()->after('numero_escalafon');
            $t->softDeletesTz();
        });
        DB::unprepared("ALTER TABLE users ADD COLUMN role rol_enum NOT NULL DEFAULT 'policia'");
        DB::unprepared("ALTER TABLE users ALTER COLUMN email DROP NOT NULL");
        DB::unprepared("ALTER TABLE users ALTER COLUMN password DROP NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("ALTER TABLE users DROP COLUMN role");
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['can_login','rango','numero_escalafon','fecha_ingreso','deleted_at']);
        });
    }
};
