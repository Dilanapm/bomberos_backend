<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>2FA</title></head>
<body>
<h1>Verificación 2FA</h1>

@if($errors->any())
  <ul>
    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
  </ul>
@endif

<form method="POST" action="{{ route('two-factor.login.store') }}">
  @csrf
  <div>
    <label>Código (Authenticator)</label>
    <input name="code" inputmode="numeric" autocomplete="one-time-code">
  </div>

  <p>O usa un código de recuperación:</p>
  <div>
    <label>Recovery code</label>
    <input name="recovery_code">
  </div>

  <button type="submit">Verificar</button>
</form>
</body>
</html>