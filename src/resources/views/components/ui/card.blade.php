@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white/90 border border-slate-200 rounded-2xl shadow-sm p-6']) }}>
  @if($title)
    <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ $title }}</h2>
  @endif
  {{ $slot }}
</div>
