<x-layouts.admin>
  <x-slot:title>Panel de Administración</x-slot:title>

  <div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-primary-5 to-primary-6 rounded-xl shadow-lg p-8 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold mb-2">Bienvenido, {{ auth()->user()->name ?? 'Administrador' }}</h3>
          <p class="text-primary-1">Panel de Administración - Cuerpo de Bomberos</p>
        </div>
        <div class="hidden md:block">
          <x-lucide-flame class="w-20 h-20 text-white opacity-20" />
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Total Users Card -->
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-primary-1 dark:bg-dark-2 rounded-lg">
            <x-lucide-users class="w-6 h-6 text-primary-5 dark:text-dark-8" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Total</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ \App\Models\User::count() }}</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Usuarios registrados</p>
      </div>

      <!-- Admins Card -->
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-accent-400 bg-opacity-10 dark:bg-dark-2 rounded-lg">
            <x-lucide-shield class="w-6 h-6 text-accent-500 dark:text-dark-8" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Roles</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ \App\Models\User::role('admin')->count() }}</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Administradores</p>
      </div>

      <!-- Active Sessions Card -->
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-success-500 bg-opacity-10 dark:bg-dark-2 rounded-lg">
            <x-lucide-activity class="w-6 h-6 text-success-500 dark:text-success-400" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Hoy</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ \App\Models\User::whereNotNull('email_verified_at')->count() }}</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Usuarios verificados</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-4 flex items-center gap-2">
        <x-lucide-zap class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Acciones Rápidas
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-4 bg-secondary-50 dark:bg-dark-1 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors group">
          <div class="flex items-center justify-center w-10 h-10 bg-primary-5 rounded-lg group-hover:scale-110 transition-transform">
            <x-lucide-user-plus class="w-5 h-5 text-white" />
          </div>
          <div>
            <p class="font-semibold text-secondary-800 dark:text-secondary-100">Nuevo Usuario</p>
            <p class="text-xs text-secondary-500 dark:text-secondary-400">Crear cuenta</p>
          </div>
        </a>

        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-4 bg-secondary-50 dark:bg-dark-1 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors group">
          <div class="flex items-center justify-center w-10 h-10 bg-secondary-600 rounded-lg group-hover:scale-110 transition-transform">
            <x-lucide-users class="w-5 h-5 text-white" />
          </div>
          <div>
            <p class="font-semibold text-secondary-800 dark:text-secondary-100">Ver Usuarios</p>
            <p class="text-xs text-secondary-400">Gestionar cuentas</p>
          </div>
        </a>

        <a href="{{ route('admin.passkeys.ui') }}" class="flex items-center gap-3 p-4 bg-secondary-50 dark:bg-dark-1 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors group">
          <div class="flex items-center justify-center w-10 h-10 bg-accent-500 rounded-lg group-hover:scale-110 transition-transform">
            <x-lucide-fingerprint class="w-5 h-5 text-white" />
          </div>
          <div>
            <p class="font-semibold text-secondary-800 dark:text-secondary-100">Mis Passkeys</p>
            <p class="text-xs text-secondary-500 dark:text-secondary-400">Gestionar accesos</p>
          </div>
        </a>

        <a href="{{ route('2fa.setup') }}" class="flex items-center gap-3 p-4 bg-secondary-50 dark:bg-dark-1 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors group">
          <div class="flex items-center justify-center w-10 h-10 bg-success-500 rounded-lg group-hover:scale-110 transition-transform">
            <x-lucide-shield-check class="w-5 h-5 text-white" />
          </div>
          <div>
            <p class="font-semibold text-secondary-800 dark:text-secondary-100">Seguridad 2FA</p>
            <p class="text-xs text-secondary-500 dark:text-secondary-400">Configurar</p>
          </div>
        </a>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-4 flex items-center gap-2">
        <x-lucide-clock class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Actividad Reciente
      </h4>
      
      @php
        $recentActivities = \App\Models\ActivityLog::with(['user', 'causer'])
          ->latest()
          ->take(10)
          ->get();
      @endphp

      @if($recentActivities->isEmpty())
        <div class="text-center py-8 text-secondary-400 dark:text-secondary-500">
          <x-lucide-inbox class="w-12 h-12 mx-auto mb-3 opacity-50" />
          <p>No hay actividad reciente para mostrar</p>
        </div>
      @else
        <div class="space-y-3">
          @foreach($recentActivities as $activity)
            <div class="flex items-start gap-3 p-3 bg-secondary-50 dark:bg-dark-1 rounded-lg hover:bg-secondary-100 dark:hover:bg-dark-2 transition-colors">
              <div class="flex-shrink-0">
                <div class="flex items-center justify-center w-10 h-10 bg-white dark:bg-dark-2 rounded-lg shadow-sm">
                  <x-dynamic-component :component="'lucide-' . $activity->icon" class="w-5 h-5 {{ $activity->color }}" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100">
                  {{ $activity->description }}
                </p>
                <div class="flex items-center gap-2 mt-1 text-xs text-secondary-500 dark:text-secondary-400">
                  @if($activity->causer)
                    <span class="flex items-center gap-1">
                      <x-lucide-user class="w-3 h-3" />
                      {{ $activity->causer->name }}
                    </span>
                    <span>•</span>
                  @endif
                  <span class="flex items-center gap-1">
                    <x-lucide-clock class="w-3 h-3" />
                    {{ $activity->created_at->diffForHumans() }}
                  </span>
                  @if($activity->ip_address)
                    <span>•</span>
                    <span class="flex items-center gap-1">
                      <x-lucide-map-pin class="w-3 h-3" />
                      {{ $activity->ip_address }}
                    </span>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</x-layouts.admin>
