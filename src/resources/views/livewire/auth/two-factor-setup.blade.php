<div class="max-w-3xl space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.zone') }}" class="inline-flex items-center gap-2 text-secondary-600 hover:text-secondary-900 transition-colors font-medium">
            <x-lucide-arrow-left class="w-5 h-5" />
            Volver al dashboard
        </a>
    </div>

    @error('code')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <!-- Estado Actual -->
    <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($this->isConfirmed)
                    <div class="flex items-center justify-center w-12 h-12 bg-success-100 rounded-lg">
                        <x-lucide-shield-check class="w-6 h-6 text-success-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-secondary-800">2FA Activado</h3>
                        <p class="text-sm text-secondary-500">Tu cuenta está protegida con autenticación de dos factores</p>
                    </div>
                @elseif ($this->isEnabled)
                    <div class="flex items-center justify-center w-12 h-12 bg-amber-100 rounded-lg">
                        <x-lucide-alert-circle class="w-6 h-6 text-amber-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-secondary-800">2FA Pendiente de Confirmar</h3>
                        <p class="text-sm text-secondary-500">Escanea el QR y confirma el código para completar la activación</p>
                    </div>
                @else
                    <div class="flex items-center justify-center w-12 h-12 bg-primary-1 rounded-lg">
                        <x-lucide-shield-off class="w-6 h-6 text-primary-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-secondary-800">2FA Desactivado</h3>
                        <p class="text-sm text-secondary-500">
                            <x-lucide-alert-triangle class="w-4 h-4 inline" />
                            Debes activar 2FA para continuar usando el sistema
                        </p>
                    </div>
                @endif
            </div>

            @if ($this->isConfirmed)
                <button 
                    type="button"
                    @click="$dispatch('confirm-modal', {
                        title: 'Desactivar 2FA',
                        message: 'Al desactivar 2FA:\n\n• Perderás acceso al panel de administración\n• Deberás reactivarlo inmediatamente\n• Tus recovery codes actuales dejarán de funcionar\n\n¿Estás seguro de continuar?',
                        confirmText: 'Desactivar',
                        cancelText: 'Cancelar',
                        icon: 'shield-off',
                        iconColor: 'text-primary-6',
                        iconBg: 'bg-primary-1',
                        onConfirm: () => { $wire.disable() }
                    })" 
                    class="px-4 py-2 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
                >
                    <x-lucide-shield-off class="w-4 h-4" />
                    Desactivar 2FA
                </button>
            @endif
        </div>
    </div>

    @error('code')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    @if (!$this->isEnabled)
            <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
                <button wire:click="enable" class="px-6 py-3 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <x-lucide-shield-check class="w-5 h-5" />
                    Habilitar 2FA
                </button>
            </div>
        @else
            <div class="space-y-6">
                @if (!$this->isConfirmed)
                    <!-- QR Code -->
                    <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
                        <h3 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                            <x-lucide-qr-code class="w-5 h-5 text-primary-5" />
                            1. Escanea el código QR
                        </h3>
                        <p class="text-sm text-secondary-600 mb-4">
                            Usa Google Authenticator, Authy o cualquier app compatible con TOTP
                        </p>
                        <div class="flex justify-center p-4 bg-secondary-50 rounded-lg">
                            {!! $this->user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>

                    <!-- Confirm Code -->
                    <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
                        <h3 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                            <x-lucide-key class="w-5 h-5 text-primary-5" />
                            2. Confirma el código
                        </h3>
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-secondary-700 mb-2">Código de 6 dígitos</label>
                                <input wire:model.defer="code" class="block w-full px-4 py-2.5 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5" placeholder="123456" maxlength="6">
                            </div>
                            <div class="flex items-end">
                                <button wire:click="confirm" class="px-6 py-2.5 bg-success-500 hover:bg-success-600 text-white rounded-lg font-semibold transition-colors flex items-center gap-2">
                                    <x-lucide-check class="w-5 h-5" />
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recovery Codes -->
                <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-secondary-800 flex items-center gap-2">
                                <x-lucide-life-buoy class="w-5 h-5 text-primary-5" />
                                Códigos de Recuperación
                            </h3>
                            <p class="text-sm text-secondary-600 mt-1">
                                Usa estos códigos si pierdes acceso a tu autenticador
                            </p>
                        </div>
                        @if ($this->isConfirmed)
                            <div class="flex gap-2">
                                <button wire:click="showRecovery" class="px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white rounded-lg font-medium transition-colors text-sm">
                                    Mostrar
                                </button>
                                <button 
                                    type="button"
                                    @click="$dispatch('confirm-modal', {
                                        title: 'Regenerar Códigos',
                                        message: '¿Regenerar códigos de recuperación?\n\nLos códigos anteriores dejarán de funcionar y deberás guardar los nuevos.',
                                        confirmText: 'Regenerar',
                                        cancelText: 'Cancelar',
                                        icon: 'alert-triangle',
                                        iconColor: 'text-amber-600',
                                        iconBg: 'bg-amber-100',
                                        onConfirm: () => { $wire.regenerateRecoveryCodes() }
                                    })"
                                    class="px-4 py-2 bg-accent-500 hover:bg-accent-600 text-white rounded-lg font-medium transition-colors text-sm"
                                >
                                    Regenerar
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($this->showRecoveryCodes)
                        <div class="mt-4">
                            @if (!$this->isConfirmed)
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-start gap-3">
                                        <x-lucide-alert-triangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p class="font-semibold text-amber-800 mb-1">¡Importante!</p>
                                            <p class="text-sm text-amber-700">
                                                Guarda estos códigos en un lugar seguro. Los necesitarás si pierdes acceso a tu autenticador.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3 p-4 bg-secondary-50 rounded-lg border border-secondary-200">
                                @foreach ($recoveryCodes as $rc)
                                    <div class="px-3 py-2 bg-white border border-secondary-300 rounded font-mono text-sm text-center">
                                        {{ $rc }}
                                    </div>
                                @endforeach
                            </div>

                            @if (!$this->isConfirmed)
                                <div class="mt-4 space-y-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="confirmedSavedRecovery" class="w-4 h-4 rounded border-secondary-300 text-primary-5 focus:ring-primary-5">
                                        <span class="text-sm font-medium text-secondary-700">He guardado estos códigos de forma segura</span>
                                    </label>

                                    <button type="button" wire:click="finish" @if(!$confirmedSavedRecovery) disabled @endif class="w-full px-6 py-3 bg-success-500 hover:bg-success-600 disabled:bg-secondary-300 disabled:cursor-not-allowed text-white rounded-lg font-semibold transition-colors">
                                        Continuar al Dashboard
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
</div>
