<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Confirmar contraseña</h1>

    <p class="mb-6 text-sm text-gray-600">Por seguridad, confirma tu contraseña para continuar.</p>

    @if($errors->any())
      <ul class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    @endif

    <form method="POST" action="{{ url('/user/confirm-password') }}">
      @csrf
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Contraseña</label>
        <input name="password" type="password" required autofocus
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Confirmar
      </button>
    </form>

    <p class="mt-6 text-center">
      <a href="{{ url('/logout') }}"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         class="text-red-600 hover:underline">
        Cerrar sesión
      </a>
    </p>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
      @csrf
    </form>
  </div>
</div>
</x-layouts.app>
