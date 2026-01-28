@props([
    'icon',
    'label' => 'Acción',
    'size' => 5,
    'variant' => 'ghost', // ghost | solid | danger
])

@php
    $base = "inline-flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500";
    $pad = "h-9 w-9";

    $variants = [
        'ghost' => "border border-gray-300 bg-white text-gray-700 hover:bg-gray-50",
        'solid' => "bg-blue-600 text-white hover:bg-blue-700",
        'danger' => "bg-red-600 text-white hover:bg-red-700",
    ];

    $classes = $base . " " . $pad . " " . ($variants[$variant] ?? $variants['ghost']);
@endphp

<button type="button" {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $label }}">
    <x-icon :name="$icon" :size="$size" title="{{ $label }}" />
</button>
