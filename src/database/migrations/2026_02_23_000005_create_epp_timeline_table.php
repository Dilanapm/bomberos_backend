<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epp_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->constrained('epp_evaluations')
                ->onDelete('cascade');

            $table->decimal('time', 8, 2);           // Segundo en el video
            $table->integer('step_detected');         // 0-5 (índice del paso)
            $table->decimal('confidence', 5, 4);      // 0.0000 - 1.0000

            $table->timestamps();

            $table->index(['evaluation_id', 'time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epp_timeline');
    }
};
