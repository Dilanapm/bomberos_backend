<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recuperar contraseña</title></head>
<body>
<h1>Recuperar contraseña</h1>

@if(session('status')) <p>{{ session('status') }}</p> @endif
@if($errors->any())
  <ul>
    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
  </ul>
@endif

<form method="POST" action="{{ route('password.email') }}">
  @csrf
  <div>
    <label>Email</label>
    <input name="email" type="email" required autofocus>
  </div>
  <button type="submit">Enviar link</button>
</form>
</body>
</html>