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
    public bool $confirmedSavedRecovery = false;
    public function markRecoverySaved(): void
    {
        $this->confirmedSavedRecovery = true;
    }
    public function finish(): void
    {
        if (! $this->confirmedSavedRecovery) {
            $this->dispatch('notify', type: 'warning', message: 'Marca que guardaste los recovery codes antes de continuar.');
            return;
        }

        $this->redirect($this->nextUrlAfter2fa(), navigate: true);

    }
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

    private function adminHasActivePasskey(): bool
    {
        $user = $this->user;

        if (! $user) return false;

        // Laragear: relation webAuthnCredentials() existe si ya integraste el paquete
        if (! method_exists($user, 'webAuthnCredentials')) {
            return false;
        }

        return $user->webAuthnCredentials()
            ->whereNull('disabled_at')
            ->exists();
    }

    private function nextUrlAfter2fa(): string
    {
        $user = $this->user;

        if (! $user) {
            return '/login';
        }

        // Si por política solo admin usa web, corta aquí:
        if (! $user->hasRole('admin')) {
            // Puedes mandarlo a /dashboard, o a una vista de "no permitido"
            return '/login';
        }

        // Admin: si no tiene passkeys -> UI, si tiene -> zona
        return $this->adminHasActivePasskey()
            ? route('admin.zone')
            : route('admin.passkeys.ui');
    }

    public function enable(): void
    {
        $user = $this->user;
        if (! $user) 
        {
            $this->redirect('/login', navigate: true);
            return;
        }
        app(EnableTwoFactorAuthentication::class)($user);

        $this->dispatch('notify', type: 'success', message: '2FA habilitado. Escanea el QR y confirma el código.');
    }

    public function confirm(): void
    {
        $this->validate([
            'code' => ['required', 'regex:/^\d{6,10}$/'],
        ]);

        $user = $this->user;
        if (! $user) {
        $this->redirect('/login', navigate: true);
        return;
        }
        app(ConfirmTwoFactorAuthentication::class)($user, $this->code);

        $this->code = '';
        $this->showRecoveryCodes = true;
        $this->confirmedSavedRecovery = false;
        // Importante: aquí NO redirigimos.
        $this->dispatch('notify', type: 'success', message: '2FA confirmado. Ahora guarda tus recovery codes.');
    }


    public function showRecovery(): void
    {
        $this->showRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = $this->user;
        if (! $user) {
        $this->redirect('/login', navigate: true);
        return;
    }
        app(GenerateNewRecoveryCodes::class)($user);

        $this->showRecoveryCodes = true;
        $this->confirmedSavedRecovery = false;
        $this->dispatch('notify', type: 'success', message: 'Recovery codes regenerados.');
    }

    public function disable(): void
    {
        $user = $this->user;
        if (! $user) {
            $this->redirect('/login', navigate: true);
            return;
        }

        // Desactivar 2FA
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->showRecoveryCodes = false;
        $this->code = '';
        
        // Mensaje para que el usuario sepa que debe reactivarlo
        $this->dispatch('notify', type: 'warning', message: '2FA desactivado. Debes reactivarlo para continuar usando el sistema.');
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
        ])->layout('components.layouts.admin', [
            'title' => 'Autenticación 2FA',
            'subtitle' => 'Configura la verificación en dos pasos'
        ]);
    }
}
