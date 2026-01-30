<x-layouts.app>
<div class="min-h-screen flex items-center justify-center bg-secondary-50 px-4">
  <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-xl border border-secondary-200">
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-accent-400 rounded-full mb-4">
        <x-lucide-shield-alert class="w-8 h-8 text-white" />
      </div>
      <h1 class="text-3xl font-bold text-secondary-800">Verificación 2FA</h1>
      <p class="text-sm text-secondary-400 mt-2">Ingresa tu código de autenticación</p>
    </div>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5">
      @csrf
      <div>
        <label class="block text-secondary-700 font-medium mb-2 flex items-center gap-2">
          <x-lucide-smartphone class="w-4 h-4 text-primary-5" />
          Código Authenticator
        </label>
        <input name="code" inputmode="numeric" autocomplete="one-time-code" value="{{ old('code') }}"
               class="w-full px-4 py-3 bg-secondary-50 border @error('code') border-primary-5 @else border-secondary-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 focus:border-transparent text-secondary-900 text-center text-2xl tracking-widest font-mono transition-all"
               placeholder="000000">
        @error('code')
          <p class="mt-1 text-sm text-primary-7 flex items-center gap-1">
            <x-lucide-alert-circle class="w-4 h-4" />
            {{ $message }}
          </p>
        @enderror
      </div>

      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-secondary-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-4 bg-white text-secondary-400 font-medium">o usa</span>
        </div>
      </div>

      <div>
        <label class="block text-secondary-700 font-medium mb-2 flex items-center gap-2">
          <x-lucide-life-buoy class="w-4 h-4 text-primary-5" />
          Código de Recuperación
        </label>
        <input name="recovery_code" value="{{ old('recovery_code') }}"
               class="w-full px-4 py-3 bg-secondary-50 border @error('recovery_code') border-primary-5 @else border-secondary-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 focus:border-transparent text-secondary-900 transition-all">
        @error('recovery_code')
          <p class="mt-1 text-sm text-primary-7 flex items-center gap-1">
            <x-lucide-alert-circle class="w-4 h-4" />
            {{ $message }}
          </p>
        @enderror
      </div>

      <button type="submit" 
              class="w-full bg-primary-5 text-white font-semibold py-3 px-4 rounded-lg hover:bg-primary-6 transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
        <x-lucide-check-circle class="w-5 h-5" />
        Verificar
      </button>
    </form>
  </div>
</div>
</x-layouts.app>