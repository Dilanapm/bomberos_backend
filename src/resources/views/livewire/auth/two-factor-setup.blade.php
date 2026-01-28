<div class="max-w-2xl mx-auto p-6">
    <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold">Activar 2FA</h1>
        <p class="text-sm text-gray-600 mt-1">
            Admin e Instructor requieren 2FA. Escanea el QR y confirma el código.
        </p>

        @error('code')
            <div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <hr class="my-6">

        @if (!$this->isEnabled)
            <button wire:click="enable" class="rounded-xl px-4 py-2 bg-black text-white">
                Habilitar 2FA
            </button>
        @else
            <div class="space-y-6">
                <div class="rounded-2xl border p-4">
                    <h2 class="font-medium">1. Escanea el QR</h2>
                    <div class="mt-4 flex justify-center">
                        <div class="mt-4 flex justify-center">
                            {!! $this->user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border p-4">
                    <h2 class="font-medium">2. Confirmar</h2>
                    <div class="mt-3 flex gap-3 items-end">
                        <div class="flex-1">
                            <label class="text-sm">Código</label>
                            <input wire:model.defer="code" class="mt-1 w-full rounded-xl border px-3 py-2"
                                placeholder="123456">
                        </div>
                        <button wire:click="confirm" class="rounded-xl px-4 py-2 bg-black text-white">
                            Confirmar
                        </button>
                    </div>

                    @if ($this->isConfirmed)
                        <p class="text-sm text-green-700 mt-3">✅ 2FA confirmado.</p>
                    @endif
                </div>

                <div class="rounded-2xl border p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="font-medium">Recovery codes</h2>
                        <x-help-bubble title="¿Qué son los recovery codes?">
                            Son códigos de respaldo para entrar si pierdes tu celular o Google Authenticator.
                            Guárdalos en un lugar seguro. Si alguien los ve, podría entrar a tu cuenta.
                        </x-help-bubble>
                        <div class="flex gap-2">
                            <button wire:click="showRecovery"
                                class="rounded-xl px-3 py-2 border text-sm">Mostrar</button>
                            <button wire:click="regenerateRecoveryCodes"
                                class="rounded-xl px-3 py-2 border text-sm">Regenerar</button>
                        </div>
                    </div>

                    @if ($this->showRecoveryCodes || $this->isConfirmed)
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($recoveryCodes as $rc)
                                <div class="rounded-xl bg-gray-50 border px-3 py-2 font-mono text-sm">
                                    {{ $rc }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-between">
                    <a href="/admin/zone" class="text-sm underline">Volver</a>
                    <a href="/admin/passkeys-ui" class="text-sm underline">Ir a Passkeys</a>
                </div>
            </div>
        @endif
        @if ($showRecoveryCodes)
            <div class="space-y-4 border-t pt-6">
                <h2 class="text-lg font-semibold text-red-600">
                    Recovery Codes ¡guárdalos!
                </h2>

                <p class="text-sm text-gray-600">
                    Estos códigos te permitirán acceder si pierdes tu autenticador.
                    <strong>Guárdalos en un lugar seguro.</strong>
                </p>

                {{-- Lista de recovery codes --}}
                <div class="grid grid-cols-2 gap-2 text-sm font-mono bg-gray-100 p-4 rounded">
                    @foreach ($recoveryCodes as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>

                {{-- ✅ CHECKBOX AQUÍ --}}
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="confirmedSavedRecovery" class="rounded border-gray-300">
                    Ya guardé mis recovery codes
                </label>

                {{-- ▶️ BOTÓN CONTINUAR --}}
                <button type="button" wire:click="finish"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                    Continuar
                </button>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('2fa-enabled', ({
                nextUrl
            }) => {
                alert('✅ 2FA activado...');
                window.location.href = nextUrl;
            });
        });
    </script>

</div>
