<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cambia el valor predeterminado de los permisos de vista a true.
     * Los usuarios nuevos tendrán acceso a todos los módulos por defecto;
     * el administrador puede revocarlos individualmente.
     */
    public function up(): void
    {
        // Cambiar el default de la columna en PostgreSQL
        DB::statement('ALTER TABLE users ALTER COLUMN can_access_ai_module SET DEFAULT true');
        DB::statement('ALTER TABLE users ALTER COLUMN can_view_student_stats SET DEFAULT true');

        // Actualizar usuarios existentes que tengan false (los que nunca fueron tocados)
        // Solo afecta a aprendices e instructores — los admins no usan estos flags
        DB::statement("
            UPDATE users
            SET can_access_ai_module = true,
                can_view_student_stats = true
            WHERE can_access_ai_module = false
              AND can_view_student_stats = false
              AND id IN (
                  SELECT model_id FROM model_has_roles
                  JOIN roles ON roles.id = model_has_roles.role_id
                  WHERE roles.name IN ('aprendiz', 'instructor')
                    AND model_has_roles.model_type = 'App\\Models\\User'
              )
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN can_access_ai_module SET DEFAULT false');
        DB::statement('ALTER TABLE users ALTER COLUMN can_view_student_stats SET DEFAULT false');
    }
};
