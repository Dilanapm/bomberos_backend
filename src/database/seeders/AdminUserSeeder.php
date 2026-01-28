<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@bomberos.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin12345!!'),
            ]
        );

        $admin->syncRoles(['admin']); // esta linea asegura que el usuario tenga el rol de admin
        

    }
}
