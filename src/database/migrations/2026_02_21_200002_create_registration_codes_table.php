<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_codes', function (Blueprint $table) {
            $table->id();

            // Código alfanumérico de 8 caracteres generado por el instructor
            $table->string('code', 8)->unique();

            // Instructor que lo generó
            $table->foreignId('instructor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Fecha de expiración: 30 minutos desde la creación
            $table->timestamp('expires_at');

            // Cuántas veces se puede usar (null = ilimitado dentro del período)
            $table->unsignedSmallInteger('max_uses')->default(50);

            // Cuántas veces se ha usado
            $table->unsignedSmallInteger('uses')->default(0);

            // Marcado como usado/invalidado manualmente por el instructor
            $table->boolean('revoked')->default(false);

            $table->timestamps();

            $table->index(['code', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_codes');
    }
};
