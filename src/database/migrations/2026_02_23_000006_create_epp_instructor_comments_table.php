<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epp_instructor_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->constrained('epp_evaluations')
                ->onDelete('cascade');
            $table->foreignId('instructor_id')
                ->constrained('users')
                ->onDelete('cascade');

            // null = comentario general sobre la evaluación completa
            $table->integer('step_number')->nullable();

            $table->text('comment');
            $table->enum('type', ['correcion', 'felicitacion', 'observacion']);

            $table->timestamps();

            $table->index('evaluation_id');
            $table->index('instructor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epp_instructor_comments');
    }
};
