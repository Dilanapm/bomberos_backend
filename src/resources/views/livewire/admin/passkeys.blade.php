@php
  $hasDisabled = isset($credentials) && $credentials->whereNotNull('disabled_at')->count() > 0;
@endphp

<div class="max-w-4xl space-y-6">

    <!-- Register Passkey Card -->
    <div class="bg-gradient-to-r from-primary-5 to-primary-6 rounded-xl shadow-lg p-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-3">
            <div class="flex items-center justify-center w-12 h-12 bg-white bg-opacity-20 rounded-lg">
              <x-lucide-fingerprint class="w-6 h-6 text-white" />
            </div>
            <h3 class="text-xl font-bold">Registrar Nueva Passkey</h3>
          </div>
          <p class="text-primary-1 mb-4">
            Usa tu huella dactilar, reconocimiento facial o clave de seguridad física para acceder de forma rápida y segura.
          </p>
          <button
            type="button"
            id="btn-register-passkey"
            class="px-6 py-3 bg-white text-primary-6 rounded-lg font-semibold hover:bg-primary-1 transition-colors flex items-center gap-2"
          >
            <x-lucide-plus class="w-5 h-5" />
            Registrar Passkey
          </button>
        </div>
        <div class="hidden lg:block">
          <x-lucide-shield-check class="w-24 h-24 text-white opacity-20" />
        </div>
      </div>

      @if ($hasDisabled)
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-sm text-primary-1 mb-3">
            <x-lucide-alert-triangle class="w-4 h-4 inline" />
            Tienes passkeys revocadas. Elimínalas antes de registrar una nueva desde el mismo dispositivo.
          </p>
          <button
            type="button"
            @click="$dispatch('confirm-modal', {
              title: 'Eliminar Passkeys Revocadas',
              message: '¿Eliminar permanentemente las passkeys revocadas?\n\nEsto es necesario para registrar una nueva desde el mismo dispositivo.\n\nLos datos se eliminarán de forma permanente.',
              confirmText: 'Eliminar',
              cancelText: 'Cancelar',
              icon: 'trash-2',
              iconColor: 'text-primary-6',
              iconBg: 'bg-primary-1',
              onConfirm: () => { $wire.deleteDisabled() }
            })"
            class="px-4 py-2 bg-primary-7 hover:bg-primary-8 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
          >
            <x-lucide-trash-2 class="w-4 h-4" />
            Eliminar Passkeys Revocadas
          </button>
        </div>
      @endif

      <p id="passkey-status" class="mt-4 text-sm text-primary-1"></p>
    </div>

    <!-- Credentials List -->
    <div class="bg-white rounded-xl shadow-md border border-secondary-200">
      <div class="p-6 border-b border-secondary-200">
        <h3 class="text-lg font-bold text-secondary-800 flex items-center gap-2">
          <x-lucide-key class="w-5 h-5 text-primary-5" />
          Credenciales Registradas
        </h3>
      </div>

      <div class="p-6">
        @if (isset($credentials) && $credentials->count())
          <div class="space-y-4">
            @foreach ($credentials as $c)
              <div class="p-4 border border-secondary-200 rounded-lg {{ $c->disabled_at ? 'bg-secondary-50 opacity-60' : 'bg-white' }}">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex-1">
                    <!-- Credential Info -->
                    <div class="flex items-center gap-3 mb-3">
                      <div class="flex items-center justify-center w-10 h-10 {{ $c->disabled_at ? 'bg-secondary-200' : 'bg-primary-1' }} rounded-lg">
                        <x-lucide-fingerprint class="w-5 h-5 {{ $c->disabled_at ? 'text-secondary-500' : 'text-primary-6' }}" />
                      </div>
                      <div>
                        <p class="font-semibold text-secondary-800">
                          {{ $c->alias ?? 'Sin nombre' }}
                          @if ($c->disabled_at)
                            <span class="ml-2 text-xs font-medium text-primary-6 bg-primary-1 px-2 py-0.5 rounded">
                              REVOCADA
                            </span>
                          @endif
                        </p>
                        <p class="text-xs text-secondary-500">
                          ID: {{ substr($c->id, 0, 8) }}... • Creada: {{ $c->created_at->format('d/m/Y H:i') }}
                        </p>
                      </div>
                    </div>

                    <!-- Alias Input -->
                    <div class="flex items-start gap-3">
                      <div class="flex-1">
                        <label class="block text-xs font-semibold text-secondary-700 mb-1">
                          Nombre identificador
                        </label>
                        <input
                          type="text"
                          wire:model.defer="alias.{{ $c->id }}"
                          placeholder="Ej: Laptop oficina, iPhone personal"
                          class="block w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5 text-sm"
                        />
                        @error('alias.' . $c->id)
                          <p class="mt-1 text-xs text-primary-6 flex items-center gap-1">
                            <x-lucide-alert-circle class="w-3 h-3" />
                            {{ $message }}
                          </p>
                        @enderror
                      </div>
                      
                      <div class="flex gap-2 mt-6">
                        <button
                          type="button"
                          wire:click="saveAlias('{{ $c->id }}')"
                          class="px-4 py-2 bg-success-500 hover:bg-success-600 text-white rounded-lg font-medium transition-colors flex items-center gap-1 text-sm"
                          title="Guardar alias"
                        >
                          <x-lucide-save class="w-4 h-4" />
                          Guardar
                        </button>

                        @if (!$c->disabled_at)
                          <button
                            type="button"
                            @click="$dispatch('confirm-modal', {
                              title: 'Revocar Passkey',
                              message: 'Al revocar esta passkey:\n\n• No podrás usarla para iniciar sesión\n• Si es tu única passkey activa, no podrás revocarla\n• Deberás registrar una nueva si eliminas todas\n\n¿Estás seguro de continuar?',
                              confirmText: 'Revocar',
                              cancelText: 'Cancelar',
                              icon: 'ban',
                              iconColor: 'text-primary-6',
                              iconBg: 'bg-primary-1',
                              onConfirm: () => { $wire.revoke('{{ $c->id }}') }
                            })"
                            class="px-4 py-2 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-medium transition-colors flex items-center gap-1 text-sm"
                            title="Revocar"
                          >
                            <x-lucide-ban class="w-4 h-4" />
                            Revocar
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
          <div class="text-center py-12">
            <div class="flex flex-col items-center justify-center text-secondary-400">
              <x-lucide-fingerprint class="w-16 h-16 mb-4 opacity-50" />
              <p class="text-lg font-semibold mb-2">No tienes passkeys registradas</p>
              <p class="text-sm">Registra tu primera passkey usando el botón de arriba</p>
            </div>
          </div>
        @endif
      </div>
    </div>

    <!-- Info Card -->
    <div class="bg-secondary-50 border border-secondary-200 rounded-xl p-6">
      <div class="flex items-start gap-3">
        <x-lucide-info class="w-5 h-5 text-secondary-600 flex-shrink-0 mt-0.5" />
        <div>
          <h4 class="font-semibold text-secondary-800 mb-2">¿Qué son las Passkeys?</h4>
          <p class="text-sm text-secondary-600 mb-3">
            Las passkeys son claves de acceso biométricas que reemplazan las contraseñas tradicionales. 
            Usa tu huella dactilar, reconocimiento facial o clave de seguridad física para iniciar sesión de forma más rápida y segura.
          </p>
          <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded">
            <p class="text-sm text-amber-800 font-medium flex items-center gap-2">
              <x-lucide-alert-triangle class="w-4 h-4" />
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
    const hasDisabledPasskeys = {{ $hasDisabled ? 'true' : 'false' }};

    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('btn-register-passkey');
      const status = document.getElementById('passkey-status');

      btn?.addEventListener('click', async () => {
        status.textContent = '';

        // Verificar si hay passkeys revocadas
        if (hasDisabledPasskeys) {
          alert('Primero debes eliminar las passkeys revocadas antes de registrar una nueva. Usa el botón "Eliminar passkeys revocadas".');
          return;
        }

        if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
          alert("Tu navegador/dispositivo no soporta Passkeys/WebAuthn.");
          return;
        }

        status.textContent = 'Abriendo registro de passkey...';

        const { success, error } = await Webpass.attest(
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
