<x-layouts.admin>
  <x-slot:title>Gestión de Usuarios</x-slot:title>
  <x-slot:subtitle>Administra cuentas y permisos del sistema</x-slot:subtitle>

  <div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Search & Filters -->
        <form method="GET" class="flex-1 flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <x-lucide-search class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
              </div>
              <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar por nombre o email..."
                class="block w-full pl-10 pr-3 py-2.5 border border-secondary-300 dark:border-dark-3 dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5 text-sm"
              />
            </div>
          </div>

          <div class="flex gap-3">
            <select
              name="role"
              class="px-4 py-2.5 border border-secondary-300 dark:border-dark-3 dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5 text-sm bg-white"
            >
              <option value="">Todos los roles</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                  {{ ucfirst($role->name) }}
                </option>
              @endforeach
            </select>

            <button
              type="submit"
              class="px-6 py-2.5 bg-secondary-600 hover:bg-secondary-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
            >
              <x-lucide-filter class="w-4 h-4" />
              Filtrar
            </button>

            @if (request('search') || request('role'))
              <a
                href="{{ route('admin.users.index') }}"
                class="px-4 py-2.5 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 rounded-lg font-medium transition-colors flex items-center gap-2"
              >
                <x-lucide-x class="w-4 h-4" />
                Limpiar
              </a>
            @endif
          </div>
        </form>

        <!-- Create Button -->
        <a
          href="{{ route('admin.users.create') }}"
          class="px-6 py-2.5 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-semibold transition-colors flex items-center gap-2 justify-center whitespace-nowrap"
        >
          <x-lucide-user-plus class="w-5 h-5" />
          Nuevo Usuario
        </a>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-secondary-200 dark:divide-dark-2">
          <thead class="bg-secondary-50 dark:bg-dark-1">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Usuario
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Email
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Rol
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Estado
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Fecha Registro
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-secondary-700 dark:text-secondary-300 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-dark-0 divide-y divide-secondary-200 dark:divide-dark-2">
            @forelse ($users as $user)
              <tr class="hover:bg-secondary-50 dark:hover:bg-dark-1 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-primary-1 dark:bg-dark-3 rounded-full flex items-center justify-center">
                      <span class="text-primary-6 dark:text-dark-8 font-bold text-sm">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-semibold text-secondary-900 dark:text-secondary-100">{{ $user->name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-700 dark:text-secondary-300">{{ $user->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @foreach ($user->roles as $role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                      {{ $role->name === 'admin' ? 'bg-primary-1 text-primary-7' : 'bg-secondary-100 text-secondary-700' }}">
                      {{ ucfirst($role->name) }}
                    </span>
                  @endforeach
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if ($user->isDisabled())
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-1 text-primary-6">
                      <x-lucide-user-x class="w-3 h-3" />
                      Desactivado
                    </span>
                  @elseif ($user->email_verified_at)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-success-200 text-success-600">
                      <x-lucide-check-circle class="w-3 h-3" />
                      Activo
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-accent-200 text-accent-500">
                      <x-lucide-alert-circle class="w-3 h-3" />
                      Pendiente
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500 dark:text-secondary-400">
                  {{ $user->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <a
                      href="{{ route('admin.users.show', $user) }}"
                      class="text-secondary-600 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-secondary-100 transition-colors"
                      title="Ver detalles"
                    >
                      <x-lucide-eye class="w-5 h-5" />
                    </a>
                    @if (!$user->hasRole('admin') || $user->id === auth()->id())
                      <a
                        href="{{ route('admin.users.edit', $user) }}"
                        class="text-accent-400 dark:text-accent-500 hover:text-accent-500 dark:hover:text-accent-300 transition-colors"
                        title="Editar"
                      >
                        <x-lucide-edit class="w-5 h-5" />
                      </a>
                    @endif
                    @if ($user->id !== auth()->id() && !$user->hasRole('admin'))
                      <div
                        x-data="{
                          formId: 'form-toggle-user-{{ $user->id }}',
                          modalData: {
                            title: @js($user->isDisabled() ? 'Activar Usuario' : 'Desactivar Usuario'),
                            message: @js($user->isDisabled() ? "¿Estás seguro de activar a {$user->name}?\n\nEl usuario podrá acceder nuevamente al sistema." : "¿Estás seguro de desactivar a {$user->name}?\n\nEl usuario no podrá acceder al sistema hasta que sea activado nuevamente."),
                            confirmText: @js($user->isDisabled() ? 'Activar' : 'Desactivar'),
                            cancelText: 'Cancelar',
                            icon: @js($user->isDisabled() ? 'user-check' : 'user-x'),
                            iconColor: @js($user->isDisabled() ? 'text-success-600' : 'text-primary-6'),
                            iconBg: @js($user->isDisabled() ? 'bg-success-100' : 'bg-primary-1')
                          },
                          confirm() {
                            $dispatch('confirm-modal', {
                              ...this.modalData,
                              onConfirm: () => { document.getElementById(this.formId).submit() }
                            });
                          }
                        }"
                        class="inline"
                      >
                        <form
                          method="POST"
                          action="{{ route('admin.users.destroy', $user) }}"
                          :id="formId"
                          class="inline"
                        >
                          @csrf
                          @method('DELETE')
                          <button
                            type="button"
                            @click="confirm()"
                            class="{{ $user->isDisabled() ? 'text-success-500 hover:text-success-700' : 'text-primary-5 hover:text-primary-2' }} transition-colors"
                            title="{{ $user->isDisabled() ? 'Activar' : 'Desactivar' }}"
                          >
                            @if ($user->isDisabled())
                              <x-lucide-user-check class="w-5 h-5" />
                            @else
                              <x-lucide-user-x class="w-5 h-5" />
                            @endif
                          </button>
                        </form>
                      </div>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-secondary-400 dark:text-secondary-500">
                    <x-lucide-users class="w-12 h-12 mb-3 opacity-50" />
                    <p class="text-sm">No se encontraron usuarios</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if ($users->hasPages())
        <div class="bg-secondary-50 dark:bg-dark-1 px-6 py-4 border-t border-secondary-200 dark:border-dark-2">
          {{ $users->links() }}
        </div>
      @endif
    </div>
  </div>
</x-layouts.admin>
