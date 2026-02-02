@props([
    'type' => 'error', // error, success, warning
    'message' => null,
])
@php
    $styles = [
        'error' => [
            'container' => 'bg-primary-2 border-primary-4 dark:bg-dark-1 dark:border-dark-3',
            'icon' => 'text-primary-7 dark:text-dark-9',
            'iconComponent' => 'alert-circle',
        ],
        'success' => [
            'container' => 'bg-primary-1 border-primary-2 dark:bg-dark-1 dark:border-success-700',
            'icon' => 'text-primary-6 dark:text-success-400',
            'iconComponent' => 'check-circle',
        ],
        'warning' => [
            'container' => 'bg-accent-400 border-accent-300 bg-opacity-10 dark:bg-dark-1 dark:border-amber-600',
            'icon' => 'text-accent-500 dark:text-amber-400',
            'iconComponent' => 'alert-triangle',
        ],
    ];
    $style = $styles[$type] ?? $styles['error'];
@endphp
@if($message || $slot->isNotEmpty())
<div {{ $attributes->merge(['class' => "px-4 py-3 text-secondary-900 dark:text-secondary-100 rounded-lg border flex items-start gap-3 {$style['container']}"]) }}>
    <x-dynamic-component :component="'lucide-' . $style['iconComponent']" class="w-5 h-5 {{ $style['icon'] }} flex-shrink-0 mt-0.5" />
    <div class="flex-1">
      <p class="font-medium">{{ $message ?? $slot }}</p>  
    </div>
</div>
@endif
