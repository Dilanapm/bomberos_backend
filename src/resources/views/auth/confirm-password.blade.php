<x-layouts.app>
<div class="min-h-screen flex items-center justify-center bg-secondary-50 dark:bg-dark-1 px-4">
  <div class="max-w-md w-full bg-white dark:bg-dark-0 p-8 rounded-xl shadow-xl border border-secondary-200 dark:border-dark-2">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-accent-500 dark:bg-dark-7 rounded-full mb-4">
        <x-lucide-shield-check class="w-8 h-8 text-white" />
      </div>
      <h1 class="text-3xl font-bold text-secondary-800 dark:text-secondary-100">Confirmar Identidad</h1>
      <p class="text-sm text-secondary-400 dark:text-secondary-500 mt-2">Por seguridad, verifica tu contraseña</p>
    </div>

    <form method="POST" action="{{ url('/user/confirm-password') }}" class="space-y-5">
      @csrf
      <div>
        <label class="block text-secondary-700 dark:text-secondary-300 font-medium mb-2 flex items-center gap-2">
          <x-lucide-lock class="w-4 h-4 text-primary-5 dark:text-dark-7" />
          Contraseña
        </label>
        <input name="password" type="password" required autofocus
               class="w-full px-4 py-3 bg-secondary-50 dark:bg-dark-1 border @error('password') border-primary-5 dark:border-dark-9 @else border-secondary-200 dark:border-dark-3 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 dark:focus:ring-dark-7 focus:border-transparent text-secondary-900 dark:text-secondary-100 transition-all">
        @error('password')
          <p class="mt-1 text-sm text-primary-7 dark:text-dark-10 flex items-center gap-1">
            <x-lucide-alert-circle class="w-4 h-4" />
            {{ $message }}
          </p>
        @enderror
      </div>
      
      <button type="submit" 
              class="w-full bg-primary-5 dark:bg-dark-7 text-white font-semibold py-3 px-4 rounded-lg hover:bg-primary-6 dark:hover:bg-dark-8 transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
        <x-lucide-check-circle class="w-5 h-5" />
        Confirmar
      </button>
    </form>

    <p class="mt-6 text-center">
      <a href="{{ url('/logout') }}"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         class="text-primary-7 dark:text-dark-9 hover:text-primary-6 dark:hover:text-dark-10 font-medium hover:underline inline-flex items-center gap-1">
        <x-lucide-log-out class="w-4 h-4" />
        Cerrar Sesión
      </a>
    </p>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
      @csrf
    </form>
  </div>
</div>
</x-layouts.app>
