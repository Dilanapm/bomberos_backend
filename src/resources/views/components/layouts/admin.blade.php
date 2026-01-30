<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Panel Admin' }} - Bomberos</title>
  
  <!-- Google Fonts: Inter (complete range) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
  
</head>
<body class="bg-secondary-50 font-sans antialiased">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <x-admin.sidebar />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <x-admin.header :title="$title ?? 'Dashboard'" :subtitle="$subtitle ?? ''" />

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
</body>
</html>
