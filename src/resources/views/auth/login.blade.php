<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login</title></head>
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
    <input name="email" type="email" required autofocus>
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

<p><a href="{{ route('password.request') }}">Olvidé mi contraseña</a></p>
</body>
</html>