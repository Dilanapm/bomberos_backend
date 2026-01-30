@props([
    'type' => 'error', // error, success, warning
    'message' => null,
])
@php
    $styles = [
        'error' => [
            'container' => 'bg-primary-2 border-primary-4',
            'icon' => 'text-primary-7',
            'iconComponent' => 'alert-circle',
        ],
        'success' => [
            'container' => 'bg-primary-1 border-primary-2',
            'icon' => 'text-primary-6',
            'iconComponent' => 'check-circle',
        ],
        'warning' => [
            'container' => 'bg-accent-400 border-accent-500',
            'icon' => 'text-accent-500',
            'iconComponent' => 'alert-triangle',
        ],
    ];
    $style = $styles[$type] ?? $styles['error'];
@endphp
@if($message || $slot->isNotEmpty())
<div {{ $attributes->merge(['class' => "px-4 py-3 text-secondary-900 rounded-lg border flex items-start gap-3 {$style['container']}"]) }}>
    <x-dynamic-component :component="'lucide-' . $style['iconComponent']" class="w-5 h-5 {{ $style['icon'] }} flex-shrink-0 mt-0.5" />
    <div class="flex-1">
      <p class="font-medium">{{ $message ?? $slot }}</p>  
    </div>
</div>
@endif
