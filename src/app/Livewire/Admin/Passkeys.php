<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Passkeys extends Component
{
    public array $alias = [];

    public function mount(): void
    {
        $this->syncAliases();
    }

    private function syncAliases(): void
    {
        $user = Auth::user();
        $creds = $user->webAuthnCredentials()->get();

        foreach ($creds as $c) {
            $this->alias[$c->id] = $c->alias ?? '';
        }
    }

    public function saveAlias(string $credentialId): void
    {
        $user = Auth::user();

        $credential = $user->webAuthnCredentials()->whereKey($credentialId)->firstOrFail();

        $newAlias = trim((string)($this->alias[$credentialId] ?? ''));

        // Validación simple “enterprise”
        if ($newAlias !== '' && (mb_strlen($newAlias) < 2 || mb_strlen($newAlias) > 50)) {
            $this->addError('alias.'.$credentialId, 'Alias debe tener 2 a 50 caracteres.');
            return;
        }

        // Si no existe columna alias, esto fallaría -> por eso mejor confirmar con getAttributes()
        $credential->alias = $newAlias !== '' ? $newAlias : null;
        $credential->save();

        $this->dispatch('notify', type: 'success', message: 'Alias actualizado exitosamente.');
    }

public function revoke(string $credentialId): void
{
    $user = Auth::user();
    $credential = $user->webAuthnCredentials()->whereKey($credentialId)->firstOrFail();

    // Verificar si es la última passkey activa
    $activeCount = $user->webAuthnCredentials()
        ->whereNull('disabled_at')
        ->count();

    if ($activeCount <= 1) {
        $this->dispatch('notify', type: 'warning', message: 'No puedes revocar tu única passkey activa. Debes mantener al menos una para acceder al sistema.');
        return;
    }

    // Revocar sin borrar (auditable)
    $credential->disabled_at = now();
    $credential->save();

    $this->dispatch('notify', type: 'success', message: 'Passkey revocada exitosamente.');
    $this->syncAliases();
}

/**
 * Elimina permanentemente las passkeys deshabilitadas.
 * Esto es necesario cuando el navegador bloquea el registro de nuevas credenciales
 * porque detecta que ya existe una (aunque esté revocada en BD).
 * 
 * NOTA: Solo elimina las YA deshabilitadas (mantiene auditoría temporal).
 */
public function deleteDisabled(): void
{
    $user = Auth::user();
    
    $count = $user->webAuthnCredentials()
        ->whereNotNull('disabled_at')
        ->forceDelete();

    $this->dispatch('notify', type: 'success', message: "Se eliminaron {$count} passkey(s) deshabilitada(s). Ahora puedes registrar una nueva.");
    $this->syncAliases();
}


    public function render()
    {
        $user = Auth::user();

        $credentials = $user->webAuthnCredentials()
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.passkeys', compact('credentials'))
            ->layout('components.layouts.admin', [
                'title' => 'Mis Passkeys',
                'subtitle' => 'Administra tus claves de acceso biométricas'
            ]);
    }
}
