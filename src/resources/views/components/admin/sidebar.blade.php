<aside class="w-64 bg-white border-r border-secondary-200 flex flex-col">
  <!-- Logo / Brand -->
  <div class="p-6 border-b border-secondary-200">
    <div class="flex items-center gap-3">
      <div class="flex items-center justify-center w-10 h-10 bg-primary-5 rounded-lg">
        <x-lucide-flame class="w-6 h-6 text-white" />
      </div>
      <div>
        <h1 class="text-lg font-bold text-secondary-800">Bomberos</h1>
        <p class="text-xs text-secondary-400">Panel Admin</p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <x-admin.nav-link href="{{ route('admin.zone') }}" :active="request()->routeIs('admin.zone')">
      <x-lucide-layout-dashboard class="w-5 h-5" />
      <span>Dashboard</span>
    </x-admin.nav-link>

    <x-admin.nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
      <x-lucide-users class="w-5 h-5" />
      <span>Gestión de Usuarios</span>
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
  <div class="p-4 border-t border-secondary-200">
    <div class="flex items-center gap-3 mb-3">
      <div class="flex items-center justify-center w-10 h-10 bg-secondary-100 rounded-full">
        <x-lucide-user class="w-5 h-5 text-secondary-600" />
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-secondary-800 truncate">{{ auth()->user()->name ?? auth()->user()->email }}</p>
        <p class="text-xs text-secondary-400">Administrador</p>
      </div>
    </div>
    
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-primary-7 hover:bg-primary-1 rounded-lg transition-colors">
        <x-lucide-log-out class="w-4 h-4" />
        <span>Cerrar Sesión</span>
      </button>
    </form>
  </div>
</aside>
