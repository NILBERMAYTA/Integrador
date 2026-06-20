<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->string('appearance', 20)->default('system');
            $table->json('light_palette');
            $table->json('dark_palette');
            $table->string('font_family', 150)->nullable();
            $table->unsignedSmallInteger('border_radius')->default(8);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_active']);
            $table->index(['is_system', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
