<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorSetup extends Component
{
    public string $code = '';
    public bool $showRecoveryCodes = false;

    public function getUserProperty()
    {
        return Auth::user();
    }

    public function getIsEnabledProperty(): bool
    {
        return ! empty($this->user?->two_factor_secret);
    }

    public function getIsConfirmedProperty(): bool
    {
        return ! empty($this->user?->two_factor_confirmed_at);
    }

    public function enable(): void
    {
        $user = $this->user;
        app(EnableTwoFactorAuthentication::class)($user);

        $this->dispatch('toast', message: '2FA habilitado. Escanea el QR y confirma el código.');
    }

    public function confirm(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'min:6', 'max:10'],
        ]);

        $user = $this->user;
        app(ConfirmTwoFactorAuthentication::class)($user, $this->code);

        $this->code = '';
        $this->showRecoveryCodes = true;

        $this->dispatch('toast', message: '2FA confirmado. Ya puedes acceder al panel.');
    }

    public function showRecovery(): void
    {
        $this->showRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = $this->user;
        app(GenerateNewRecoveryCodes::class)($user);

        $this->showRecoveryCodes = true;
        $this->dispatch('toast', message: 'Recovery codes regenerados.');
    }

    public function render()
    {
        $user = $this->user;

        $recoveryCodes = [];
        if ($user && $this->isEnabled && ($this->showRecoveryCodes || $this->isConfirmed)) {
            $recoveryCodes = $user->recoveryCodes() ?? [];
        }

        return view('livewire.auth.two-factor-setup', [
            'recoveryCodes' => $recoveryCodes,
        ])->layout('layouts.app', ['title' => '2FA Setup']);
    }
}
