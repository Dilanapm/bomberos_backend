<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('trainings');
    }

    public function down(): void
    {
        // Estas tablas han sido reemplazadas por las tablas EPP.
        // No se recrean en el rollback.
    }
};
