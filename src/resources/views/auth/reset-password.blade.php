<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset password</title></head>
<body>
<h1>Restablecer contraseña</h1>

@if($errors->any())
  <ul>
    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
  </ul>
@endif

<form method="POST" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ request()->route('token') }}">
  <div>
    <label>Email</label>
    <input name="email" type="email" value="{{ old('email', request('email')) }}" required>
  </div>
  <div>
    <label>Nueva contraseña</label>
    <input name="password" type="password" required>
  </div>
  <div>
    <label>Confirmar contraseña</label>
    <input name="password_confirmation" type="password" required>
  </div>
  <button type="submit">Actualizar</button>
</form>
</body>
</html>