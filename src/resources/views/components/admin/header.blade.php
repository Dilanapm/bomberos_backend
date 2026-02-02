<header class="bg-white dark:bg-dark-0 border-b border-secondary-200 dark:border-dark-2 px-6 py-4 transition-colors">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <!-- Mobile menu button -->
      <button 
        x-data
        @click="$store.sidebar.toggle()" 
        type="button" 
        class="lg:hidden p-2 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors"
      >
        <x-lucide-menu class="w-6 h-6" />
      </button>

      <div>
        <h2 class="text-2xl font-bold text-secondary-800 dark:text-secondary-100">{{ $title ?? 'Dashboard' }}</h2>
        @if(isset($subtitle))
          <p class="text-sm text-secondary-400 dark:text-secondary-400 mt-1">{{ $subtitle }}</p>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-4">
      <!-- Theme Toggle -->
      <x-admin.theme-toggle />

      <!-- Notifications placeholder -->
      <button type="button" class="relative p-2 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors">
        <x-lucide-bell class="w-5 h-5" />
        <span class="absolute top-1 right-1 w-2 h-2 bg-primary-5 dark:bg-dark-10 rounded-full"></span>
      </button>

      <!-- Settings -->
      <a href="{{ route('2fa.setup') }}" class="p-2 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg transition-colors">
        <x-lucide-settings class="w-5 h-5" />
      </a>
    </div>
  </div>
</header>
