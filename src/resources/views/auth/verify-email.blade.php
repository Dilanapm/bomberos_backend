<x-layouts.app>
<div class="min-h-screen flex items-center justify-center bg-secondary-50 px-4">
  <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-xl border border-secondary-200">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-5 rounded-full mb-4">
        <x-lucide-mail-check class="w-8 h-8 text-white" />
      </div>
      <h1 class="text-3xl font-bold text-secondary-800">Verifica tu Email</h1>
      <p class="text-sm text-secondary-400 mt-2">Cuerpo de Bomberos</p>
    </div>

    @if(session('status') == 'verification-link-sent')
      <x-alert type="success" class="mb-6">
        Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
      </x-alert>
    @endif

    <!-- Info Message -->
    <div class="bg-secondary-50 border border-secondary-200 rounded-lg p-6 mb-6">
      <div class="flex items-start gap-3">
        <x-lucide-info class="w-5 h-5 text-secondary-600 flex-shrink-0 mt-0.5" />
        <div>
          <p class="text-sm text-secondary-700 mb-2">
            Gracias por registrarte. Antes de continuar, por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te enviamos.
          </p>
          <p class="text-sm text-secondary-600">
            Si no recibiste el correo, puedes solicitar uno nuevo.
          </p>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button 
          type="submit"
          class="w-full px-6 py-3 bg-primary-5 hover:bg-primary-6 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <x-lucide-send class="w-5 h-5" />
          Reenviar Email de Verificación
        </button>
      </form>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button 
          type="submit"
          class="w-full px-6 py-3 bg-secondary-100 hover:bg-secondary-200 text-secondary-700 font-semibold rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <x-lucide-log-out class="w-5 h-5" />
          Cerrar Sesión
        </button>
      </form>
    </div>

    <!-- Help Text -->
    <div class="mt-6 text-center">
      <p class="text-xs text-secondary-500">
        Revisa tu carpeta de spam si no encuentras el correo
      </p>
    </div>
  </div>
</div>
</x-layouts.app>
