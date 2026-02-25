<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epp_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Puntajes generales
            $table->decimal('general_score', 5, 2);         // 0.00 - 100.00
            $table->integer('total_steps')->default(6);
            $table->integer('steps_completed');
            $table->boolean('correct_order');

            // Estadísticas del video
            $table->decimal('duration_seconds', 8, 2);      // Ej: 58.30 s
            $table->integer('total_frames');
            $table->integer('frames_with_detection');
            $table->decimal('detection_rate', 5, 2);        // Ej: 93.20 %

            // Resultado general
            $table->enum('status', ['aprobado', 'reprobado', 'incompleto']);
            $table->text('recommendations')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index('general_score');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epp_evaluations');
    }
};
