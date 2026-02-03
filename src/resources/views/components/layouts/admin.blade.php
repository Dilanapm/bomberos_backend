<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Panel Admin' }} - Bomberos</title>
  <!-- Prevent dark mode flash -->
  <script>
    // Este script DEBE ejecutarse antes de que se renderice el body
    (function() {
      const theme = localStorage.getItem('theme');
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      if (theme === 'dark' || (!theme && prefersDark)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>
  
  <!-- Google Fonts: Inter (complete range) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('sidebar', {
        open: false,
        toggle() { this.open = !this.open },
        close() { this.open = false }
      })
    })
  </script>
</head>
<body class="bg-secondary-50 dark:bg-dark-1 font-sans antialiased transition-colors">
  <div class="flex h-screen overflow-hidden">
    <!-- Mobile sidebar overlay -->
    <div 
      x-data
      x-show="$store.sidebar.open" 
      @click="$store.sidebar.close()"
      x-transition:enter="transition-opacity ease-linear duration-200"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition-opacity ease-linear duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 bg-secondary-900 bg-opacity-50 z-40 lg:hidden"
      style="display: none;"
    ></div>

    <!-- Sidebar -->
    <x-admin.sidebar />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <x-admin.header :title="$title ?? 'Panel de Administración'" :subtitle="$subtitle ?? ''" />

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-6">
        {{ $slot }}
      </main>
    </div>
  </div>

  <!-- Confirm Modal Component -->
  <x-confirm-modal />

  <!-- Toast Notifications -->
  <x-toast-notifications />

  @livewireScripts
  @stack('scripts')

  <!-- Flash Messages to Toast -->
  @if (session('success'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        window.dispatchEvent(new CustomEvent('notify', {
          detail: {
            type: 'success',
            message: @js(session('success'))
          }
        }));
      });
    </script>
  @endif

  @if (session('error'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        window.dispatchEvent(new CustomEvent('notify', {
          detail: {
            type: 'error',
            message: @js(session('error'))
          }
        }));
      });
    </script>
  @endif

  @if (session('warning'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        window.dispatchEvent(new CustomEvent('notify', {
          detail: {
            type: 'warning',
            message: @js(session('warning'))
          }
        }));
      });
    </script>
  @endif

  @if (session('info'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        window.dispatchEvent(new CustomEvent('notify', {
          detail: {
            type: 'info',
            message: @js(session('info'))
          }
        }));
      });
    </script>
  @endif
</body>
</html>
