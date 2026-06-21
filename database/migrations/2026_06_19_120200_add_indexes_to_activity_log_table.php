<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name', 'activity_log');

        Schema::connection($connection)->table($table, function (Blueprint $table) {
            // Acelera paneles de "actividad por usuario/dia" y filtros por tipo.
            // (log_name, subject y causer ya estan indexados por la migracion de Spatie.)
            $table->index('created_at', 'activity_log_created_at_index');
            $table->index(['subject_type', 'event'], 'activity_log_subject_event_index');
        });
    }

    public function down(): void
    {
        $connection = config('activitylog.database_connection');
        $table = config('activitylog.table_name', 'activity_log');

        Schema::connection($connection)->table($table, function (Blueprint $table) {
            $table->dropIndex('activity_log_created_at_index');
            $table->dropIndex('activity_log_subject_event_index');
        });
    }
};
