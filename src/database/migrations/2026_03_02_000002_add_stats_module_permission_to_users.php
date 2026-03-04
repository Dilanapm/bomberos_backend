<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el permiso de acceso al módulo de estadísticas/reportes para aprendices.
     * Separado de can_access_ai_module (módulo EPP/IA) y de
     * can_view_student_stats (para instructores).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_access_stats_module')
                ->default(true)
                ->after('can_view_student_stats');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_access_stats_module');
        });
    }
};
