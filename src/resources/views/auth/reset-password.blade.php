<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Restablecer contraseña</h1>

    @if($errors->any())
      <ul class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ request()->route('token') }}">
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email</label>
        <input name="email" type="email" value="{{ old('email', request('email')) }}" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Nueva contraseña</label>
        <input name="password" type="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Confirmar contraseña</label>
        <input name="password_confirmation" type="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <button type="submit" 
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Actualizar contraseña
      </button>
    </form>
  </div>
</div>
</x-layouts.app>