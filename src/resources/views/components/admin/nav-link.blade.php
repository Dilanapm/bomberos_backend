@props(['active' => false])

@php
    $classes = $active 
        ? 'flex items-center gap-3 px-4 py-3 bg-primary-5 text-white rounded-lg font-medium transition-all'
        : 'flex items-center gap-3 px-4 py-3 text-secondary-600 hover:bg-secondary-100 rounded-lg font-medium transition-all hover:text-secondary-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
