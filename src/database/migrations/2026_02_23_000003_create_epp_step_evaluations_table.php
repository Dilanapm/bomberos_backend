<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epp_step_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->constrained('epp_evaluations')
                ->onDelete('cascade');

            $table->integer('step_number');          // 1-6
            $table->string('step_name');             // "Pantalón y botas"
            $table->decimal('score', 5, 4);          // 0.0000 - 1.0000
            $table->enum('status', ['correcto', 'incorrecto']);
            $table->boolean('detected');
            $table->text('feedback');

            // Timestamps del paso en el video
            $table->decimal('time_start', 8, 2)->nullable();
            $table->decimal('time_end', 8, 2)->nullable();
            $table->decimal('duration', 8, 2)->nullable();

            $table->timestamps();

            $table->index('evaluation_id');
            $table->index('step_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epp_step_evaluations');
    }
};
