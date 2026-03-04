<aside 
  x-data
  class="w-64 bg-white dark:bg-dark-0 border-r border-secondary-200 dark:border-dark-2 flex flex-col fixed lg:static inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out lg:translate-x-0"
  :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
  <!-- Logo / Brand -->
  <div class="p-6 border-b border-secondary-200 dark:border-dark-2">
    <div class="flex items-center gap-3">
      <div class="flex items-center justify-center w-16 h-16 rounded-lg">
                    <img src="{{ asset('storage/logos/logo_bomberos.webp') }}" alt="Logo Bomberos"
                        class="w-full h-full object-cover">
      </div>
      <div>
        <h1 class="text-lg font-bold text-secondary-800 dark:text-secondary-100">Bomberos</h1>
        <p class="text-xs text-secondary-400 dark:text-secondary-500">Panel Admin</p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto" @click="window.innerWidth < 1024 && $store.sidebar.close()">
    <x-admin.nav-link href="{{ route('admin.zone') }}" :active="request()->routeIs('admin.zone')">
      <x-lucide-layout-dashboard class="w-5 h-5" />
      <span>Panel de Administración</span>
    </x-admin.nav-link>

    <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
      <x-lucide-users class="w-5 h-5" />
      <span>Gestión de Usuarios</span>
    </x-admin.nav-link>

    <x-admin.nav-link href="{{ route('admin.statistics') }}" :active="request()->routeIs('admin.statistics')">
      <x-lucide-bar-chart-2 class="w-5 h-5" />
      <span>Estadísticas</span>
    </x-admin.nav-link>

    <x-admin.nav-link href="{{ route('admin.passkeys.ui') }}" :active="request()->routeIs('admin.passkeys.*')">
      <x-lucide-fingerprint class="w-5 h-5" />
      <span>Mis Passkeys</span>
    </x-admin.nav-link>

    <x-admin.nav-link href="{{ route('2fa.setup') }}" :active="request()->routeIs('2fa.setup')">
      <x-lucide-shield-check class="w-5 h-5" />
      <span>Autenticación 2FA</span>
    </x-admin.nav-link>
  </nav>

  <!-- User Info / Logout -->
  <div class="p-4 border-t border-secondary-200 dark:border-dark-2">
    <div class="flex items-center gap-3 mb-3">
      <div class="flex items-center justify-center w-10 h-10 bg-secondary-100 dark:bg-dark-2 rounded-full">
        <x-lucide-user class="w-5 h-5 text-secondary-600 dark:text-secondary-300" />
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100 truncate">{{ auth()->user()->name ?? auth()->user()->email }}</p>
        <p class="text-xs text-secondary-400 dark:text-secondary-500">Administrador</p>
      </div>
    </div>
    
    <form method="POST" action="{{ route('logout') }}" id="logout-form">
      @csrf
      <button 
        type="button" 
        @click="$dispatch('confirm-modal', {
          title: 'Cerrar Sesión',
          message: '¿Estás seguro que deseas cerrar sesión?',
          confirmText: 'Cerrar Sesión',
          cancelText: 'Cancelar',
          icon: 'log-out',
          iconColor: 'text-primary-600 dark:text-dark-9',
          iconBg: 'bg-primary-100 dark:bg-dark-2',
          onConfirm: () => document.getElementById('logout-form').submit()
        })"
        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-primary-7 dark:text-dark-9 hover:bg-primary-1 dark:hover:bg-dark-2 rounded-lg transition-colors"
      >
        <x-lucide-log-out class="w-4 h-4" />
        <span>Cerrar Sesión</span>
      </button>
    </form>
  </div>
</aside>
