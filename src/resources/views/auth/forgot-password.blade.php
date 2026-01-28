<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Recuperar contraseña</h1>
    
    <p class="mb-6 text-sm text-gray-600">Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>

    @if(session('status')) 
      <p class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('status') }}</p>
    @endif
    
    @if($errors->has('email'))
      <p class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ $errors->first('email') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email</label>
        <input name="email" type="email" required autofocus
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Enviar enlace
      </button>
    </form>

    <p class="mt-6 text-center">
      <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Volver al login</a>
    </p>
  </div>
</div>
</x-layouts.app>