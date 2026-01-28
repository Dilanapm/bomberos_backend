@props(['steps' => [], 'current' => 1])

<ol class="flex items-center gap-3">
  @foreach($steps as $i => $label)
    @php $n = $i + 1; @endphp
    <li class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
        {{ $n <= $current ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700' }}">
        {{ $n }}
      </span>
      <span class="text-sm {{ $n === $current ? 'font-semibold text-slate-900' : 'text-slate-600' }}">
        {{ $label }}
      </span>
    </li>
    @if($n < count($steps))
      <span class="h-px w-8 bg-slate-200"></span>
    @endif
  @endforeach
</ol>
