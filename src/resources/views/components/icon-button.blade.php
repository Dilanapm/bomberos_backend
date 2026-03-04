@props([
    'icon',
    'label' => 'Acción',
    'size' => 5,
    'variant' => 'ghost', // ghost | solid | danger
])

@php
    $base = "inline-flex items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-primary-5 dark:focus:ring-dark-7";
    $pad = "h-9 w-9";

    $variants = [
        'ghost' => "border border-secondary-200 dark:border-dark-3 bg-white dark:bg-dark-1 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-dark-2",
        'solid' => "bg-info-600 dark:bg-info-700 text-white hover:bg-info-700 dark:hover:bg-info-700",
        'danger' => "bg-primary-5 dark:bg-dark-8 text-white hover:bg-primary-6 dark:hover:bg-dark-9",
    ];

    $classes = $base . " " . $pad . " " . ($variants[$variant] ?? $variants['ghost']);
@endphp

<button type="button" {{ $attributes->merge(['class' => $classes]) }} aria-label="{{ $label }}">
    <x-icon :name="$icon" :size="$size" title="{{ $label }}" />
</button>
