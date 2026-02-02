@props(['active' => false])

@php
    $classes = $active 
        ? 'flex items-center gap-3 px-4 py-3 bg-primary-5 dark:bg-dark-7 text-white rounded-lg font-medium transition-all'
        : 'flex items-center gap-3 px-4 py-3 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-dark-2 rounded-lg font-medium transition-all hover:text-secondary-900 dark:hover:text-secondary-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
