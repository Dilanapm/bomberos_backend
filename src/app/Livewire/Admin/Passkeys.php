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

        session()->flash('status', 'Alias actualizado.');
    }

public function revoke(string $credentialId): void
{
    $user = Auth::user();
    $credential = $user->webAuthnCredentials()->whereKey($credentialId)->firstOrFail();

    // Revocar sin borrar (auditable)
    $credential->disabled_at = now();
    $credential->save();

    session()->flash('status', 'Passkey revocada.');
    $this->syncAliases();
}


    public function render()
    {
        $user = Auth::user();

        $credentials = $user->webAuthnCredentials()
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin.passkeys', compact('credentials'))
            ->layout('layouts.app', ['title' => 'Passkeys (Admin)']);
    }
}
