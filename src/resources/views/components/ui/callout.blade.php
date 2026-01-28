@props(['type' => 'info'])

@php
  $styles = [
    'info' => 'bg-blue-50 border-blue-200 text-blue-900',
    'warn' => 'bg-amber-50 border-amber-200 text-amber-900',
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-900',
    'danger' => 'bg-rose-50 border-rose-200 text-rose-900',
  ][$type] ?? 'bg-slate-50 border-slate-200 text-slate-900';
@endphp

<div {{ $attributes->merge(['class' => "border rounded-xl p-4 $styles"]) }}>
  {{ $slot }}
</div>
