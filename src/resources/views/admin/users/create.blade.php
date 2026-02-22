<x-layouts.admin>
  <x-slot:title>Crear Usuario</x-slot:title>
  <x-slot:subtitle>Registra una nueva cuenta en el sistema</x-slot:subtitle>

  <div class="max-w-3xl">
    <!-- Back Button -->
    <div class="mb-6">
      <a
        href="{{ route('admin.users.index') }}"
        class="inline-flex items-center gap-2 text-secondary-600 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-secondary-100 transition-colors font-medium"
      >
        <x-lucide-arrow-left class="w-5 h-5" />
        Volver a usuarios
      </a>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 dark:bg-dark-1 border border-secondary-700 dark:border-secondary-200 rounded-xl p-4 mb-6">
      <div class="flex items-start gap-3">
        <x-lucide-info class="w-5 h-5 text-blue-600 dark:text-secondary-400 flex-shrink-0 mt-0.5" />
        <div>
          <h3 class="text-sm font-semibold text-secondary-900 dark:text-secondary-100 mb-1">Roles Disponibles</h3>
          <ul class="text-sm text-secondary-900 dark:text-secondary-300 space-y-1">
            <li><strong>Admin:</strong> Acceso completo al panel de administración web</li>
            <li><strong>Instructor:</strong> Solo acceso a la aplicación móvil</li>
            <li><strong>Aprendiz:</strong> Solo acceso a la aplicación móvil</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-8">
      <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6" novalidate x-data="{ selectedRole: '{{ old('role', '') }}' }">
        @csrf

        <!-- Name Field -->
        <div>
          <label for="name" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
            Nombre Completo
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-user class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
            <input
              id="name"
              type="text"
              name="name"
              value="{{ old('name') }}"
              required
              autofocus
              class="block w-full pl-10 pr-3 py-2.5 border @error('name') border-primary-5 @else border-secondary-300 dark:border-dark-3 @enderror dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
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
          <label for="email" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
            Correo Electrónico
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-mail class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email') }}"
              required
              class="block w-full pl-10 pr-3 py-2.5 border @error('email') border-primary-5 @else border-secondary-300 dark:border-dark-3 @enderror dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
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
          <label for="role" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
            Rol de Usuario
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-shield class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
            <select
              id="role"
              name="role"
              required
              x-model="selectedRole"
              class="block w-full pl-10 pr-3 py-2.5 border @error('role') border-primary-5 @else border-secondary-300 dark:border-dark-3 @enderror dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5 bg-white appearance-none"
            >
              <option value="">Seleccionar rol...</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                  {{ ucfirst($role->name) }}
                </option>
              @endforeach
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <x-lucide-chevron-down class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
          </div>
          @error('role')
            <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
              <x-lucide-alert-circle class="w-4 h-4" />
              {{ $message }}
            </p>
          @enderror
        </div>

        <!-- View Permissions Section (Show only for non-admin roles) -->
        <div x-show="selectedRole === 'instructor' || selectedRole === 'aprendiz'" x-cloak class="border-t border-secondary-200 dark:border-dark-2 pt-6">
          <h3 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-2 flex items-center gap-2">
            <x-lucide-eye class="w-5 h-5 text-primary-5 dark:text-dark-7" />
            Permisos de Vistas
          </h3>
          <p class="text-sm text-secondary-600 dark:text-secondary-300 mb-4">
            Habilita o deshabilita el acceso a diferentes módulos en la aplicación móvil.
          </p>

          <div class="space-y-4">
            <!-- AI Module Permission -->
            <div class="flex items-start gap-3 p-4 bg-secondary-50 dark:bg-dark-1 rounded-lg border border-secondary-200 dark:border-dark-2">
              <input
                type="checkbox"
                id="can_access_ai_module"
                name="can_access_ai_module"
                value="1"
                {{ old('can_access_ai_module') ? 'checked' : '' }}
                class="mt-1 w-5 h-5 text-primary-5 border-secondary-300 dark:border-dark-3 rounded focus:ring-2 focus:ring-primary-5"
              />
              <div class="flex-1">
                <label for="can_access_ai_module" class="block text-sm font-semibold text-secondary-800 dark:text-secondary-100 cursor-pointer">
                  Módulo de Inteligencia Artificial
                </label>
                <p class="text-xs text-secondary-500 dark:text-secondary-400 mt-1">
                  Permite acceder al asistente de IA para consultas y ayuda en tiempo real.
                </p>
              </div>
              <x-lucide-bot class="w-5 h-5 text-primary-5 dark:text-dark-7 flex-shrink-0" />
            </div>

            <!-- Student Stats Permission (Only for instructors) -->
            <div x-show="selectedRole === 'instructor'" class="flex items-start gap-3 p-4 bg-secondary-50 dark:bg-dark-1 rounded-lg border border-secondary-200 dark:border-dark-2">
              <input
                type="checkbox"
                id="can_view_student_stats"
                name="can_view_student_stats"
                value="1"
                {{ old('can_view_student_stats') ? 'checked' : '' }}
                class="mt-1 w-5 h-5 text-primary-5 border-secondary-300 dark:border-dark-3 rounded focus:ring-2 focus:ring-primary-5"
              />
              <div class="flex-1">
                <label for="can_view_student_stats" class="block text-sm font-semibold text-secondary-800 dark:text-secondary-100 cursor-pointer">
                  Estadísticas de Aprendices
                </label>
                <p class="text-xs text-secondary-500 dark:text-secondary-400 mt-1">
                  Permite visualizar el progreso, calificaciones y estadísticas de los aprendices asignados.
                </p>
              </div>
              <x-lucide-bar-chart-3 class="w-5 h-5 text-primary-5 dark:text-dark-7 flex-shrink-0" />
            </div>

            <!-- Info Banner -->
            <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
              <x-lucide-info class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
              <p class="text-sm text-blue-800 dark:text-blue-200">
                Estos permisos solo aplican para la aplicación móvil. Los cambios se reflejarán inmediatamente después de crear el usuario.
              </p>
            </div>
          </div>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
            Contraseña
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-lock class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
            <input
              id="password"
              type="password"
              name="password"
              required
              class="block w-full pl-10 pr-3 py-2.5 border @error('password') border-primary-5 @else border-secondary-300 dark:border-dark-3 @enderror dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
              placeholder="Mínimo 8 caracteres"
            />
          </div>
          @error('password')
            <p class="mt-2 text-sm text-primary-6 flex items-center gap-1">
              <x-lucide-alert-circle class="w-4 h-4" />
              {{ $message }}
            </p>
          @enderror
          <p class="mt-2 text-xs text-secondary-500 dark:text-secondary-400">
            Debe contener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.
          </p>
        </div>

        <!-- Password Confirmation Field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-2">
            Confirmar Contraseña
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <x-lucide-lock class="w-5 h-5 text-secondary-400 dark:text-secondary-500" />
            </div>
            <input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              required
              class="block w-full pl-10 pr-3 py-2.5 border border-secondary-300 dark:border-dark-3 dark:bg-dark-1 dark:text-secondary-100 rounded-lg focus:ring-2 focus:ring-primary-5 focus:border-primary-5"
              placeholder="Repetir contraseña"
            />
          </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-secondary-200 dark:border-dark-2 pt-6">
          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3">
            <a
              href="{{ route('admin.users.index') }}"
              class="px-6 py-2.5 border border-secondary-300 dark:border-dark-3 text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-dark-2 rounded-lg font-semibold transition-colors"
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
