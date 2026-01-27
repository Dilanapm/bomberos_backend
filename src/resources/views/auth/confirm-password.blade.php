<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirmar contraseña</title>
</head>
<body>
  <h1>Confirmar contraseña</h1>

  <p>Por seguridad, confirma tu contraseña para continuar.</p>

  @if($errors->any())
    <ul>
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  @endif

  <form method="POST" action="{{ url('/user/confirm-password') }}">
    @csrf
    <div>
      <label>Contraseña</label>
      <input name="password" type="password" required autofocus>
    </div>
    <button type="submit">Confirmar</button>
  </form>

  <p><a href="{{ url('/logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Cerrar sesión
  </a></p>

  <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
    @csrf
  </form>
</body>
</html>
