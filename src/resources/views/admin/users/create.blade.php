<x-layouts.admin>
  <x-slot:title>Crear Usuario</x-slot:title>
  <x-slot:subtitle>Registra una nueva cuenta en el sistema</x-slot:subtitle>

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
      <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
        @csrf

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
              value="{{ old('name') }}"
              required
              autofocus
              class="block w-full pl-10 pr-3 py-2.5 border @error('name') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
              placeholder="Ej: Juan Pérez Gómez"
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
              value="{{ old('email') }}"
              required
              class="block w-full pl-10 pr-3 py-2.5 border @error('email') border-primary-5 @else border-secondary-300 @enderror rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
              placeholder="usuario@bomberos.gob.ec"
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
              <option value="">Seleccionar rol...</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
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

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-semibold text-secondary-700 mb-2">
            Contraseña
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-lock class="w-5 h-5 text-secondary-400" />
            </div>
            <input
              id="password"
              type="password"
              name="password"
              required
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
          <p class="mt-2 text-xs text-secondary-500">
            Debe contener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.
          </p>
        </div>

        <!-- Password Confirmation Field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-semibold text-secondary-700 mb-2">
            Confirmar Contraseña
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-lock class="w-5 h-5 text-secondary-400" />
            </div>
            <input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              required
              class="block w-full pl-10 pr-3 py-2.5 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
              placeholder="Repetir contraseña"
            />
          </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-secondary-200 pt-6">
          <!-- Action Buttons -->
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
              <x-lucide-check class="w-5 h-5" />
              Crear Usuario
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-layouts.admin>
