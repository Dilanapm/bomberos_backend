<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login</title>
</head>
<body>
<h1>Login</h1>

@if(session('status')) <p>{{ session('status') }}</p> @endif
@if($errors->any())
  <ul>
    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
  </ul>
@endif

<form method="POST" action="{{ route('login') }}">
  @csrf
  <div>
    <label>Email</label>
    <input id="email" name="email" type="email" required autofocus>
  </div>
  <div>
    <label>Password</label>
    <input name="password" type="password" required>
  </div>
  <div>
    <label><input type="checkbox" name="remember"> Remember</label>
  </div>
  <button type="submit">Entrar</button>
</form>

<hr>

<h2>Admin</h2>
<p>Si eres administrador, puedes iniciar con Passkey (TouchID/FaceID/Windows Hello).</p>
<button type="button" id="btn-passkey">Entrar con Passkey</button>

<p><a href="{{ route('password.request') }}">Olvidé mi contraseña</a></p>

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
</body>
</html>
