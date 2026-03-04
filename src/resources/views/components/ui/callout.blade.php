@props(['type' => 'info'])

@php
  $styles = [
    'info'    => 'bg-secondary-50 dark:bg-dark-1 border-info-500 text-secondary-700 dark:text-secondary-300',
    'warn'    => 'bg-accent-200 dark:bg-dark-1 border-accent-400 text-secondary-700 dark:text-secondary-300',
    'success' => 'bg-success-200 dark:bg-dark-1 border-success-400 text-secondary-700 dark:text-secondary-300',
    'danger'  => 'bg-primary-1 dark:bg-dark-1 border-primary-4 text-secondary-700 dark:text-secondary-300',
  ][$type] ?? 'bg-secondary-50 dark:bg-dark-1 border-secondary-200 text-secondary-700 dark:text-secondary-300';
@endphp

<div {{ $attributes->merge(['class' => "border rounded-xl p-4 $styles"]) }}>
  {{ $slot }}
</div>
