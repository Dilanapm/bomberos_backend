<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epp_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                ->constrained('epp_evaluations')
                ->onDelete('cascade');

            $table->integer('step_number')->nullable();
            $table->enum('error_type', [
                'paso_omitido',
                'orden_incorrecto',
                'mala_ejecucion',
                'tiempo_insuficiente',
                'deteccion_fallida',
            ]);
            $table->text('description');
            $table->enum('severity', ['baja', 'media', 'alta']);

            $table->timestamps();

            $table->index('evaluation_id');
            $table->index(['evaluation_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epp_errors');
    }
};
