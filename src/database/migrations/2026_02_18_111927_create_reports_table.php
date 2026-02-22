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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quien genera el reporte
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // A quien se asigna
            $table->string('report_type'); // incident, training_summary, performance, equipment, safety
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable(); // fire, rescue, medical, equipment, other
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'in_review', 'resolved', 'closed', 'rejected'])->default('pending');
            $table->json('metadata')->nullable(); // Datos adicionales (ubicación, equipos involucrados, etc)
            $table->json('attachments')->nullable(); // URLs de archivos adjuntos
            $table->text('resolution')->nullable(); // Descripción de la resolución
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['report_type', 'priority']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
