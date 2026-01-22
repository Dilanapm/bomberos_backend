<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles base
        Role::findOrCreate('admin');
        Role::findOrCreate('instructor');
        Role::findOrCreate('aprendiz');

        // (Opcional) Permisos luego:
        // Permission::findOrCreate('manage_users');
        // Role::findByName('admin')->givePermissionTo('manage_users');
    }
}
