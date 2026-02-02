@props([
    'icon',
    'label' => 'Acción',
    'size' => 5,
    'variant' => 'ghost', // ghost | solid | danger
])

@php
    $base = "inline-flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400";
    $pad = "h-9 w-9";

    $variants = [
        'ghost' => "border border-gray-300 dark:border-dark-3 bg-white dark:bg-dark-1 text-gray-700 dark:text-secondary-300 hover:bg-gray-50 dark:hover:bg-dark-2",
        'solid' => "bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800",
        'danger' => "bg-red-600 dark:bg-dark-8 text-white hover:bg-red-700 dark:hover:bg-dark-9",
    ];

    $classes = $base . " " . $pad . " " . ($variants[$variant] ?? $variants['ghost']);
@endphp

<button type="button" {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $label }}">
    <x-icon :name="$icon" :size="$size" title="{{ $label }}" />
</button>
