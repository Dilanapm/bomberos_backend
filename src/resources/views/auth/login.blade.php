<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Login</h1>

    @if(session('status')) 
      <p class="mb-4 text-green-600">{{ session('status') }}</p>
    @endif
    
    @if($errors->any())
      <ul class="mb-4 text-red-600">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="mb-4">
        <label class="block text-gray-700 mb-2 flex items-center gap-2">
          <x-lucide-fingerprint class="w-5 h-5" />
          Email
        </label>
        <input id="email" name="email" type="email" required autofocus
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Password</label>
        <input name="password" type="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="mb-4">
        <label class="flex items-center">
          <input type="checkbox" name="remember" class="mr-2"> Remember
        </label>
      </div>
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Entrar
      </button>
    </form>

    <hr class="my-6">

    <div>
      <h2 class="text-xl font-semibold mb-2">Admin</h2>
      <p class="mb-4 text-sm text-gray-600">Si eres administrador, puedes iniciar con Passkey (TouchID/FaceID/Windows Hello).</p>
      <button type="button" id="btn-passkey"
              class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition">
        Entrar con Passkey
      </button>
    </div>

    <p class="mt-6 text-center">
      <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Olvidé mi contraseña</a>
    </p>
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

  btn?.addEventListener('click', async () => {
    const email = emailInput?.value?.trim();

    if (!email) {
      alert("Ingresa tu email (admin) para buscar tus passkeys.");
      return;
    }

    if (typeof Webpass === 'undefined' || Webpass.isUnsupported()) {
      alert("Tu navegador/dispositivo no soporta Passkeys/WebAuthn.");
      return;
    }

    // Opciones y login en el path público de passkeys-login
    const { success, error } = await Webpass.assert(
      { path: "/passkeys-login/options", body: { email } },
      "/passkeys-login"
    );

    if (!success) {
      alert(error ?? "No se pudo autenticar con Passkey.");
      return;
    }

    // Si el backend autenticó, redirigimos
    window.location.replace("/admin/zone");
  });
});
</script>
</x-layouts.app>
