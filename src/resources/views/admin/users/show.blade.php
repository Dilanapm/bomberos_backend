<x-layouts.admin>
  <x-slot:title>Detalles del Usuario</x-slot:title>
  <x-slot:subtitle>Información completa de {{ $user->name }}</x-slot:subtitle>

  <div class="max-w-4xl">
    <!-- Back Button -->
    <div class="mb-6">
      <a
        href="{{ route('admin.users.index') }}"
        class="inline-flex items-center gap-2 text-secondary-600 hover:text-secondary-900 transition-colors font-medium"
      >
        <x-lucide-arrow-left class="w-5 h-5" />
        Volver a usuarios
      </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Info Card -->
      <div class="lg:col-span-2 space-y-6">
        <!-- User Profile -->
        <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
              <div class="h-16 w-16 bg-primary-1 rounded-full flex items-center justify-center">
                <span class="text-primary-6 font-bold text-2xl">
                  {{ strtoupper(substr($user->name, 0, 2)) }}
                </span>
              </div>
              <div>
                <h3 class="text-xl font-bold text-secondary-800">{{ $user->name }}</h3>
                <p class="text-secondary-500">{{ $user->email }}</p>
              </div>
            </div>
            <a
              href="{{ route('admin.users.edit', $user) }}"
              class="px-4 py-2 bg-accent-500 hover:bg-accent-600 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
            >
              <x-lucide-edit class="w-4 h-4" />
              Editar
            </a>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-secondary-50 rounded-lg">
              <div class="flex items-center gap-2 text-secondary-600 mb-1">
                <x-lucide-mail class="w-4 h-4" />
                <span class="text-xs font-semibold uppercase">Email</span>
              </div>
              <p class="text-secondary-900 font-medium">{{ $user->email }}</p>
            </div>

            <div class="p-4 bg-secondary-50 rounded-lg">
              <div class="flex items-center gap-2 text-secondary-600 mb-1">
                <x-lucide-shield class="w-4 h-4" />
                <span class="text-xs font-semibold uppercase">Rol</span>
              </div>
              <div class="flex gap-2">
                @foreach ($user->roles as $role)
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $role->name === 'admin' ? 'bg-primary-1 text-primary-7' : 'bg-secondary-200 text-secondary-700' }}">
                    {{ ucfirst($role->name) }}
                  </span>
                @endforeach
              </div>
            </div>

            <div class="p-4 bg-secondary-50 rounded-lg">
              <div class="flex items-center gap-2 text-secondary-600 mb-1">
                <x-lucide-calendar class="w-4 h-4" />
                <span class="text-xs font-semibold uppercase">Fecha de Registro</span>
              </div>
              <p class="text-secondary-900 font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="p-4 bg-secondary-50 rounded-lg">
              <div class="flex items-center gap-2 text-secondary-600 mb-1">
                <x-lucide-check-circle class="w-4 h-4" />
                <span class="text-xs font-semibold uppercase">Estado</span>
              </div>
              @if ($user->email_verified_at)
                <span class="inline-flex items-center gap-1 text-success-700 font-medium">
                  <x-lucide-check-circle class="w-4 h-4" />
                  Verificado
                </span>
              @else
                <span class="inline-flex items-center gap-1 text-amber-700 font-medium">
                  <x-lucide-alert-circle class="w-4 h-4" />
                  No Verificado
                </span>
              @endif
            </div>
          </div>
        </div>

        <!-- Permissions Card -->
        @if ($user->permissions->isNotEmpty())
          <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
            <h4 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
              <x-lucide-key class="w-5 h-5 text-primary-5" />
              Permisos Especiales
            </h4>
            <div class="flex flex-wrap gap-2">
              @foreach ($user->permissions as $permission)
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-accent-50 text-accent-700 border border-accent-200">
                  {{ $permission->name }}
                </span>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <!-- Sidebar Stats -->
      <div class="space-y-6">
        <!-- Account Status -->
        <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
          <h4 class="text-sm font-bold text-secondary-700 uppercase mb-4">Estado de la Cuenta</h4>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <x-lucide-mail class="w-4 h-4 text-secondary-400" />
                <span class="text-sm text-secondary-600">Email Verificado</span>
              </div>
              @if ($user->email_verified_at)
                <x-lucide-check class="w-5 h-5 text-success-600" />
              @else
                <x-lucide-x class="w-5 h-5 text-primary-5" />
              @endif
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <x-lucide-shield-check class="w-4 h-4 text-secondary-400" />
                <span class="text-sm text-secondary-600">2FA Activado</span>
              </div>
              @if ($user->two_factor_secret)
                <x-lucide-check class="w-5 h-5 text-success-600" />
              @else
                <x-lucide-x class="w-5 h-5 text-secondary-400" />
              @endif
            </div>

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <x-lucide-fingerprint class="w-4 h-4 text-secondary-400" />
                <span class="text-sm text-secondary-600">Passkeys</span>
              </div>
              <span class="text-sm font-semibold text-secondary-900">
                {{ $user->webAuthnCredentials()->count() }}
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
          <h4 class="text-sm font-bold text-secondary-700 uppercase mb-4">Acciones</h4>
          <div class="space-y-2">
            <a
              href="{{ route('admin.users.edit', $user) }}"
              class="flex items-center gap-3 px-4 py-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg transition-colors group"
            >
              <x-lucide-edit class="w-5 h-5 text-accent-500 group-hover:scale-110 transition-transform" />
              <span class="text-sm font-medium text-secondary-700">Editar Usuario</span>
            </a>

            @if ($user->id !== auth()->id())
              <form
                method="POST"
                action="{{ route('admin.users.destroy', $user) }}"
                @submit.prevent="$dispatch('confirm-modal', {
                  title: 'Eliminar Usuario',
                  message: '¿Estás seguro de eliminar a {{ addslashes($user->name) }}?\n\nEsta acción no se puede deshacer y se eliminarán todos los datos asociados.',
                  confirmText: 'Eliminar',
                  cancelText: 'Cancelar',
                  icon: 'trash-2',
                  iconColor: 'text-primary-6',
                  iconBg: 'bg-primary-1',
                  onConfirm: () => { $el.closest('form').submit() }
                })"
              >
                @csrf
                @method('DELETE')
                <button
                  type="submit"
                  class="w-full flex items-center gap-3 px-4 py-3 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors group"
                >
                  <x-lucide-trash-2 class="w-5 h-5 text-primary-6 group-hover:scale-110 transition-transform" />
                  <span class="text-sm font-medium text-primary-7">Eliminar Usuario</span>
                </button>
              </form>
            @endif
          </div>
        </div>

        <!-- Activity Summary -->
        <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-6">
          <h4 class="text-sm font-bold text-secondary-700 uppercase mb-4">Resumen</h4>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-secondary-600">Creado hace</span>
              <span class="text-sm font-semibold text-secondary-900">
                {{ $user->created_at->diffForHumans() }}
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-secondary-600">Última actualización</span>
              <span class="text-sm font-semibold text-secondary-900">
                {{ $user->updated_at->diffForHumans() }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-layouts.admin>
