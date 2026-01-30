<x-layouts.app>
<div class="min-h-screen flex items-center justify-center bg-secondary-50 px-4">
  <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-xl border border-secondary-200">
    <!-- Header con logo de bomberos -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-5 rounded-full mb-4">
        <x-lucide-flame class="w-8 h-8 text-white" />
      </div>
      <h1 class="text-3xl font-bold text-secondary-800">Iniciar Sesión</h1>
      <p class="text-sm text-secondary-400 mt-2">Cuerpo de Bomberos</p>
    </div>

    @if(session('status')) 
      <x-alert type="success" :message="session('status')" class="mb-4" />
    @endif

    {{-- Mostrar error genérico para prevenir enumeración de usuarios --}}
    @if($errors->any())
      <x-alert type="error" :message="$errors->first()" class="mb-4" />
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
      @csrf
      <div>
        <label class="block text-secondary-700 font-medium mb-2 flex items-center gap-2">
          <x-lucide-mail class="w-4 h-4 text-primary-5" />
          Correo Electrónico
        </label>
        <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
               class="w-full px-4 py-3 bg-secondary-50 border @error('email') border-primary-5 @else border-secondary-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 focus:border-transparent text-secondary-900 transition-all">
      </div>
      
      <div>
        <label class="block text-secondary-700 font-medium mb-2 flex items-center gap-2">
          <x-lucide-lock class="w-4 h-4 text-primary-5" />
          Contraseña
        </label>
        <input name="password" type="password" required
               class="w-full px-4 py-3 bg-secondary-50 border @error('password') border-primary-5 @else border-secondary-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-5 focus:border-transparent text-secondary-900 transition-all">
      </div>
      
      <div class="flex items-center justify-between">
        <label class="flex items-center text-secondary-600 cursor-pointer">
          <input type="checkbox" name="remember" class="mr-2 w-4 h-4 accent-primary-5 rounded"> 
          <span class="text-sm">Recordarme</span>
        </label>
        <a href="{{ route('password.request') }}" class="text-sm text-primary-5 hover:text-primary-6 font-medium hover:underline">
          ¿Olvidaste tu contraseña?
        </a>
      </div>
      
      <button type="submit" 
              class="w-full bg-success-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-success-600 transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
        <x-lucide-log-in class="w-5 h-5" />
        Entrar
      </button>
    </form>

    <div class="relative my-8">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-secondary-200"></div>
      </div>
      <div class="relative flex justify-center text-sm">
        <span class="px-4 bg-white text-secondary-400 font-medium">o continúa con</span>
      </div>
    </div>

    <div>
      <!-- Error message para Passkey -->
      <div id="passkey-error" class="hidden mb-4">
        <x-alert type="error">
          <p id="passkey-error-message" class="font-medium"></p>
        </x-alert>
      </div>

      <button type="button" id="btn-passkey"
              class="w-full bg-secondary-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-secondary-700 transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
        <x-lucide-fingerprint class="w-5 h-5" />
        Acceso con Passkey
      </button>
      <p class="mt-3 text-xs text-center text-secondary-400">
        TouchID • FaceID • Windows Hello
      </p>
    </div>
  </div>
</div>

<script>
/**
 * Mantener CSRF habilitado:
 * Webpass usa fetch internamente; aquí aseguramos el header X-CSRF-TOKEN.
 */
(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) return;

  const _fetch = window.fetch;
  window.fetch = (input, init = {}) => {
    init.headers = new Headers(init.headers || {});
    init.headers.set('X-CSRF-TOKEN', token);
    init.headers.set('X-Requested-With', 'XMLHttpRequest');
    return _fetch(input, init);
  };
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/@laragear/webpass@2/dist/webpass.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btn-passkey');
  const emailInput = document.getElementById('email');
  const errorDiv = document.getElementById('passkey-error');
  const errorMessage = document.getElementById('passkey-error-message');

  // Función para mostrar error
  const showError = (message) => {
    errorMessage.textContent = message;
    errorDiv.classList.remove('hidden');
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
      errorDiv.classList.add('hidden');
    }, 5000);
  };

  // Función para ocultar error
  const hideError = () => {
    errorDiv.classList.add('hidden');
  };

  btn?.addEventListener('click', async () => {
    hideError();
    const email = emailInput?.value?.trim();

    if (!email) {
      showError("Por favor, ingresa tu correo electrónico para continuar con Passkey.");
      emailInput?.focus();
      return;
    }

    if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
      showError("Tu navegador/dispositivo no soporta Passkeys/WebAuthn.");
      return;
    }

    // Deshabilitar botón mientras procesa
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');

    try {
      const { success, error } = await Webpass.assert(
        { path: "/passkeys-login/options", body: { email } },
        "/passkeys-login"
      );

      if (!success) {
        // Traducir errores comunes de WebAuthn al español
        let errorMsg = error ?? "No se pudo autenticar con Passkey. Verifica tu dispositivo.";
        
        if (error && error.includes('AssertionCancelled')) {
          errorMsg = "Autenticación cancelada. Intenta nuevamente cuando estés listo.";
        } else if (error && error.includes('NotAllowedError')) {
          errorMsg = "Permiso denegado. Verifica tu configuración de seguridad.";
        } else if (error && error.includes('InvalidStateError')) {
          errorMsg = "Esta credencial no es válida para este dispositivo.";
        } else if (error && error.includes('NotFoundError')) {
          errorMsg = "No se encontró ninguna Passkey para este correo.";
        }
        
        showError(errorMsg);
        return;
      }

      // Si el backend autenticó, redirigimos
      window.location.replace("/admin/zone");
    } catch (err) {
      // Manejo de errores de red o excepciones inesperadas
      let errorMsg = "Error al procesar Passkey. Intenta nuevamente.";
      
      if (err.message && err.message.includes('AssertionCancelled')) {
        errorMsg = "Autenticación cancelada. Intenta nuevamente cuando estés listo.";
      } else if (err.message && err.message.includes('cancelled')) {
        errorMsg = "Proceso cancelado por el usuario.";
      }
      
      showError(errorMsg);
    } finally {
      // Rehabilitar botón
      btn.disabled = false;
      btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  });
});
</script>
</x-layouts.app>
