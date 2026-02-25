@php
    $hasDisabled = false; // revocar ahora elimina directamente
@endphp

<div class="max-w-4xl space-y-6">

    <!-- Register Passkey Card -->
    <div class="rounded-xl bg-gradient-to-r from-primary-5 to-primary-6 p-6 text-white shadow-lg">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-white bg-opacity-20">
                        <x-lucide-fingerprint class="h-6 w-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold">Registrar Nueva Passkey</h3>
                </div>
                <p class="mb-4 text-primary-1">
                    Usa tu huella dactilar, reconocimiento facial o clave de seguridad física para acceder de forma
                    rápida y segura.
                </p>
                <button type="button" id="btn-register-passkey"
                    class="flex items-center gap-2 rounded-lg bg-white px-6 py-3 font-semibold text-primary-6 transition-colors hover:bg-primary-1">
                    <x-lucide-plus class="h-5 w-5" />
                    Registrar Passkey
                </button>
            </div>
            <div class="hidden lg:block">
                <x-lucide-shield-check class="h-24 w-24 text-white opacity-20" />
            </div>
        </div>

        <p id="passkey-status" class="mt-4 text-sm text-primary-1"></p>
    </div>

    <!-- Credentials List -->
    <div class="rounded-xl border border-secondary-200 bg-white shadow-md dark:border-dark-2 dark:bg-dark-0">
        <div class="border-b border-secondary-200 p-6 dark:border-dark-2">
            <h3 class="flex items-center gap-2 text-lg font-bold text-secondary-800 dark:text-secondary-100">
                <x-lucide-key class="h-5 w-5 text-primary-5 dark:text-dark-7" />
                Credenciales Registradas
            </h3>
        </div>

        <div class="p-6">
            @if (isset($credentials) && $credentials->count())
                <div class="space-y-4">
                    @foreach ($credentials as $c)
                        <div
                            class="{{ $c->disabled_at ? 'bg-secondary-50 dark:bg-dark-1 opacity-60' : 'bg-white dark:bg-dark-0' }} rounded-lg border border-secondary-200 p-4 dark:border-dark-2">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <!-- Credential Info -->
                                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="{{ $c->disabled_at ? 'bg-secondary-200 dark:bg-dark-2' : 'bg-primary-1 dark:bg-dark-2' }} flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg">
                                                <x-lucide-fingerprint
                                                    class="{{ $c->disabled_at ? 'text-secondary-500 dark:text-secondary-600' : 'text-primary-6 dark:text-dark-7' }} h-5 w-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate font-semibold text-secondary-800 dark:text-secondary-100">
                                                    {{ $c->alias ?? 'Sin nombre' }}
                                                    @if ($c->disabled_at)
                                                        <span
                                                            class="ml-2 whitespace-nowrap rounded bg-primary-1 px-2 py-0.5 text-xs font-medium text-primary-6">
                                                            REVOCADA
                                                        </span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-secondary-500 dark:text-secondary-400">
                                                    ID: {{ substr($c->id, 0, 8) }}
                                                    <span class="hidden sm:inline"><br></span>
                                                    <span class="sm:hidden"> • </span>
                                                    Creada: {{ $c->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alias Input -->
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start">
                                        <div class="flex-1">
                                            <label
                                                class="mb-1 block text-xs font-semibold text-secondary-700 dark:text-secondary-300">
                                                Nombre identificador
                                            </label>
                                            <input type="text" wire:model.defer="alias.{{ $c->id }}"
                                                placeholder="Ej: Laptop oficina, iPhone personal"
                                                class="block w-full rounded-lg border border-secondary-300 px-3 py-2 text-sm focus:border-primary-5 focus:ring-2 focus:ring-primary-5 dark:border-dark-3 dark:bg-dark-1 dark:text-secondary-100" />
                                            @error('alias.' . $c->id)
                                                <p class="mt-1 flex items-center gap-1 text-xs text-primary-6">
                                                    <x-lucide-alert-circle class="h-3 w-3" />
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="flex flex-col gap-2 sm:flex-row lg:mt-6">
                                            <button type="button" wire:click="saveAlias('{{ $c->id }}')"
                                                class="flex items-center justify-center gap-1 rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-success-600"
                                                title="Guardar alias">
                                                <x-lucide-save class="h-4 w-4" />
                                                <span>Guardar</span>
                                            </button>

                                            @if (!$c->disabled_at)
                                                <button type="button"
                                                    @click="$dispatch('confirm-modal', {
                              title: 'Revocar Passkey',
                              message: 'Al revocar esta passkey:\n\n• No podrás usarla para iniciar sesión\n\n¿Estás seguro de continuar?',
                              confirmText: 'Revocar',
                              cancelText: 'Cancelar',
                              icon: 'ban',
                              iconColor: 'text-primary-6',
                              iconBg: 'bg-primary-1',
                              onConfirm: () => { $wire.revoke('{{ $c->id }}') }
                            })"
                                                    class="flex items-center justify-center gap-1 rounded-lg bg-primary-5 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-6"
                                                    title="Revocar">
                                                    <x-lucide-ban class="h-4 w-4" />
                                                    <span>Revocar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-secondary-400 dark:text-secondary-500">
                        <x-lucide-fingerprint class="mb-4 h-16 w-16 opacity-50" />
                        <p class="mb-2 text-lg font-semibold">No tienes passkeys registradas</p>
                        <p class="text-sm">Registra tu primera passkey usando el botón de arriba</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Info Card -->
    <div class="rounded-xl border border-secondary-200 bg-secondary-50 p-6 dark:border-dark-2 dark:bg-dark-1">
        <div class="flex items-start gap-3">
            <x-lucide-info class="mt-0.5 h-5 w-5 flex-shrink-0 text-secondary-600 dark:text-secondary-400" />
            <div>
                <h4 class="mb-2 font-semibold text-secondary-800 dark:text-secondary-100">¿Qué son las Passkeys?</h4>
                <p class="mb-3 text-sm text-secondary-600 dark:text-secondary-300">
                    Las passkeys son credenciales de acceso basadas en criptografía que pueden
                    reemplazar contraseñas. Se desbloquean con biometría o con el método de desbloqueo
                    del dispositivo PIN/contraseña o una llave de seguridad física.
                </p>
                <div
                    class="bg-amber-50 border-amber-400 dark:border-amber-600 rounded border-l-4 p-3 dark:bg-dark-3 dark:text-secondary-300">
                    <p class="flex items-center gap-2 text-sm font-medium text-secondary-600 dark:text-secondary-300">
                        <x-lucide-alert-triangle class="h-4 w-4" />
                        Importante: Debes mantener al menos una passkey activa para acceder al sistema.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</div>

@push('scripts')
    <script>
        (() => {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!token) return;

            const _fetch = window.fetch;
            window.fetch = (input, init = {}) => {
                init.headers = new Headers(init.headers || {});
                init.headers.set('X-CSRF-TOKEN', token);
                init.headers.set('X-Requested-With', 'XMLHttpRequest');
                return _fetch(input, init);
            };
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
    <script>
        const hasDisabledPasskeys = false; // revocar ahora elimina directamente

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-register-passkey');
            const status = document.getElementById('passkey-status');

            btn?.addEventListener('click', async () => {
                status.textContent = '';

                if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
                    alert("Tu navegador/dispositivo no soporta Passkeys/WebAuthn.");
                    return;
                }

                status.textContent = 'Abriendo registro de passkey...';

                const {
                    success,
                    error
                } = await Webpass.attest(
                    "/admin/passkeys/options",
                    "/admin/passkeys"
                );

                if (!success) {
                    status.textContent = '';
                    alert(error ?? "No se pudo registrar la passkey.");
                    return;
                }

                status.textContent = 'Passkey registrada exitosamente. Recargando...';
                window.location.reload();
            });
        });
    </script>
@endpush
