<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Admin2UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin2@bomberos.local';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador 2',
                'password' => Hash::make('Admin2026!@#'),
            ]
        );

        // Asegura rol admin (Spatie)
        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        // Opcional: forzar que empiece "sin 2FA" y "sin passkeys"
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Si quieres borrar passkeys previas (por si re-ejecutas):
        if (method_exists($user, 'webAuthnCredentials')) {
            $user->webAuthnCredentials()->delete();
        }
    }
}
