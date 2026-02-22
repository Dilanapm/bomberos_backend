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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('training_type'); // ppe_recognition, quiz, simulation, practical
            $table->string('module'); // EPP, primeros_auxilios, rescate, incendios
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed', 'cancelled'])->default('not_started');
            $table->decimal('score', 5, 2)->nullable(); // Puntación 0-100
            $table->integer('duration_minutes')->nullable(); // Duración en minutos
            $table->json('ai_feedback')->nullable(); // Feedback de la IA
            $table->json('results')->nullable(); // Resultados detallados
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'status']);
            $table->index(['training_type', 'module']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
