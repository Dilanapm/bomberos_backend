<x-layouts.app>
<div class="min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Verificación 2FA</h1>

    @if($errors->any())
      <ul class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}">
      @csrf
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Código (Authenticator)</label>
        <input name="code" inputmode="numeric" autocomplete="one-time-code"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="my-6 text-center text-gray-500">— o —</div>

      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Código de recuperación</label>
        <input name="recovery_code"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <button type="submit" 
              class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
        Verificar
      </button>
    </form>
  </div>
</div>
</x-layouts.app>