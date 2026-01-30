<x-layouts.admin>
  <x-slot:title>Editar Usuario</x-slot:title>
  <x-slot:subtitle>Modifica la información de {{ $user->name }}</x-slot:subtitle>

  <div class="max-w-3xl">
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

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-md border border-secondary-200 p-8">
      <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Name Field -->
        <div>
          <label for="name" class="block text-sm font-semibold text-secondary-700 mb-2">
            Nombre Completo
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-user class="w-5 h-5 text-secondary-400" />
            </div>
            <input
              id="name"
              type="text"
              name="name"
              value="{{ old('name', $user->name) }}"
              required
              autofocus
              class="block w-full pl-10 pr-3 py-2.5 border @error('name') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
            />
          </div>
          @error('name')
            <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
              <x-lucide-alert-circle class="w-4 h-4" />
              {{ $message }}
            </p>
          @enderror
        </div>

        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-semibold text-secondary-700 mb-2">
            Correo Electrónico
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-mail class="w-5 h-5 text-secondary-400" />
            </div>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email', $user->email) }}"
              required
              class="block w-full pl-10 pr-3 py-2.5 border @error('email') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
            />
          </div>
          @error('email')
            <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
              <x-lucide-alert-circle class="w-4 h-4" />
              {{ $message }}
            </p>
          @enderror
        </div>

        <!-- Role Field -->
        <div>
          <label for="role" class="block text-sm font-semibold text-secondary-700 mb-2">
            Rol de Usuario
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-shield class="w-5 h-5 text-secondary-400" />
            </div>
            <select
              id="role"
              name="role"
              required
              class="block w-full pl-10 pr-3 py-2.5 border @error('role') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5 bg-white appearance-none"
            >
              @foreach ($roles as $role)
                <option
                  value="{{ $role->name }}"
                  {{ (old('role', $user->roles->first()->name ?? '') === $role->name) ? 'selected' : '' }}
                >
                  {{ ucfirst($role->name) }}
                </option>
              @endforeach
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <x-lucide-chevron-down class="w-5 h-5 text-secondary-400" />
            </div>
          </div>
          @error('role')
            <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
              <x-lucide-alert-circle class="w-4 h-4" />
              {{ $message }}
            </p>
          @enderror
        </div>

        <!-- Divider -->
        <div class="border-t border-secondary-200 pt-6">
          <h3 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
            <x-lucide-key class="w-5 h-5 text-primary-5" />
            Cambiar Contraseña (Opcional)
          </h3>
          <p class="text-sm text-secondary-600 mb-4">
            Deja estos campos vacíos si no deseas cambiar la contraseña actual.
          </p>

          <!-- Password Field -->
          <div class="space-y-6">
            <div>
              <label for="password" class="block text-sm font-semibold text-secondary-700 mb-2">
                Nueva Contraseña
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <x-lucide-lock class="w-5 h-5 text-secondary-400" />
                </div>
                <input
                  id="password"
                  type="password"
                  name="password"
                  class="block w-full pl-10 pr-3 py-2.5 border @error('password') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
                  placeholder="Mínimo 8 caracteres"
                />
              </div>
              @error('password')
                <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
                  <x-lucide-alert-circle class="w-4 h-4" />
                  {{ $message }}
                </p>
              @enderror
            </div>

            <!-- Password Confirmation Field -->
            <div>
              <label for="password_confirmation" class="block text-sm font-semibold text-secondary-700 mb-2">
                Confirmar Nueva Contraseña
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <x-lucide-lock class="w-5 h-5 text-secondary-400" />
                </div>
                <input
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  class="block w-full pl-10 pr-3 py-2.5 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
                  placeholder="Repetir contraseña"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="border-t border-secondary-200 pt-6">
          <div class="flex items-center justify-end gap-3">
            <a
              href="{{ route('admin.users.index') }}"
              class="px-6 py-2.5 border border-secondary-300 text-secondary-700 rounded-lg font-semibold hover:bg-secondary-50 transition-colors"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="px-6 py-2.5 bg-primary-5 hover:bg-primary-6 text-white rounded-lg font-semibold transition-colors flex items-center gap-2"
            >
              <x-lucide-save class="w-5 h-5" />
              Guardar Cambios
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-layouts.admin>
