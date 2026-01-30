<header class="bg-white border-b border-secondary-200 px-6 py-4">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-secondary-800">{{ $title ?? 'Dashboard' }}</h2>
      @if(isset($subtitle))
        <p class="text-sm text-secondary-400 mt-1">{{ $subtitle }}</p>
      @endif
    </div>

    <div class="flex items-center gap-4">
      <!-- Notifications placeholder -->
      <button type="button" class="relative p-2 text-secondary-600 hover:bg-secondary-100 rounded-lg transition-colors">
        <x-lucide-bell class="w-5 h-5" />
        <span class="absolute top-1 right-1 w-2 h-2 bg-primary-5 rounded-full"></span>
      </button>

      <!-- Settings -->
      <a href="{{ route('2fa.setup') }}" class="p-2 text-secondary-600 hover:bg-secondary-100 rounded-lg transition-colors">
        <x-lucide-settings class="w-5 h-5" />
      </a>
    </div>
  </div>
</header>
