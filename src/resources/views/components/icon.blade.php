@props([
    'name',
    'set' => 'lucide',   // <-- por defecto lucide
    'as' => 'span',
    'href' => null,
])

@php
    $tag = in_array($as, ['span','button','a']) ? $as : 'span';
    $base = 'inline-flex items-center justify-center';
    $iconName = $set . '-' . $name; // ej: lucide-fingerprint
    $iconClass = $attributes->get('class', 'w-5 h-5');
@endphp

<{{ $tag }}
    @if($tag === 'a' && $href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $base]) }}
>
    @svg($iconName, $iconClass)
</{{ $tag }}>
