<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles primero (admin, instructor, aprendiz)
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Usuario administrador (solo acceso web, no usa la API)
        $this->call(AdminUserSeeder::class);

        // 3. Instructores, aprendices y evaluaciones EPP de demostración
        $this->call(EppDemoSeeder::class);
    }
}
