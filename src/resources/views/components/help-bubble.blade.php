@props([
    'title' => null,
    'icon' => '❔',
])

<div {{ $attributes->merge(['class' => 'relative group inline-flex items-center']) }}>
    <button
        type="button"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700
               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        aria-label="Ayuda"
    >
        <span class="text-sm leading-none">{{ $icon }}</span>
    </button>

    <div
        class="pointer-events-none absolute left-1/2 top-full z-50 mt-2 w-72 -translate-x-1/2 opacity-0
               transition-opacity duration-150 group-hover:opacity-100 group-focus-within:opacity-100"
    >
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-lg">
            @if($title)
                <div class="mb-1 text-sm font-semibold text-gray-900">{{ $title }}</div>
            @endif

            <div class="text-sm text-gray-700">
                {{ $slot }}
            </div>

            <div class="mt-3 text-xs text-gray-500">
                Tip: Puedes copiar/guardar esto en un gestor de contraseñas.
            </div>
        </div>

        <div class="mx-auto h-0 w-0 border-x-8 border-x-transparent border-b-8 border-b-gray-200"></div>
    </div>
</div>
