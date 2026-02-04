<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type'); // user.created, user.updated, user.activated, etc.
            $table->text('description'); // Descripción legible de la acción
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Usuario afectado
            $table->foreignId('causer_id')->nullable()->constrained('users')->onDelete('set null'); // Quien realizó la acción
            $table->json('properties')->nullable(); // Datos adicionales (cambios, metadata)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['log_type', 'created_at']);
            $table->index('user_id');
            $table->index('causer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
