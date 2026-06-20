<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('light_theme_id')
                ->nullable()
                ->after('theme_id')
                ->constrained('themes')
                ->nullOnDelete();
            $table->foreignId('dark_theme_id')
                ->nullable()
                ->after('light_theme_id')
                ->constrained('themes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dark_theme_id');
            $table->dropConstrainedForeignId('light_theme_id');
        });
    }
};
