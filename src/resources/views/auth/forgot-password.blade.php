<x-layouts.app>
<div class="min-h-screen flex items-center justify-center bg-secondary-50 px-4">
  <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-xl border border-secondary-200">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-accent-400 rounded-full mb-4">
        <x-lucide-key-round class="w-8 h-8 text-white" />
      </div>
      <h1 class="text-3xl font-bold text-secondary-800">Recuperar Contraseña</h1>
      <p class="text-sm text-secondary-400 mt-2">Te enviaremos un enlace de restablecimiento</p>
    </div>

    @if(session('status')) 
      <x-alert type="success" :message="session('status')" class="mb-4" />
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
      @csrf
      <div>
        <label class="block text-secondary-700 font-medium mb-2 flex items-center gap-2">
          <x-lucide-mail class="w-4 h-4 text-primary-5" />
          Correo Electrónico
        </label>
        <input name="email" type="email" required autofocus value="{{ old('email') }}"
               class="w-full px-4 py-3 bg-secondary-50 border @error('email') border-primary-5 @else border-secondary-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 focus:border-transparent text-secondary-900 transition-all">
        @error('email')
          <p class="mt-1 text-sm text-primary-7 flex items-center gap-1">
            <x-lucide-alert-circle class="w-4 h-4" />
            {{ $message }}
          </p>
        @enderror
      </div>
      
      <button type="submit" 
              class="w-full bg-primary-5 text-white font-semibold py-3 px-4 rounded-lg hover:bg-primary-6 transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
        <x-lucide-send class="w-5 h-5" />
        Enviar Enlace
      </button>
    </form>

    <p class="mt-6 text-center">
      <a href="{{ route('login') }}" class="text-primary-5 hover:text-primary-6 font-medium hover:underline inline-flex items-center gap-1">
        <x-lucide-arrow-left class="w-4 h-4" />
        Volver al inicio de sesión
      </a>
    </p>
  </div>
</div>
</x-layouts.app>