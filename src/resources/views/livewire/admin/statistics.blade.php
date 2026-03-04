<div class="space-y-6">

    {{-- ── Header card ───────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-primary-5 to-primary-6 rounded-xl shadow-lg p-8 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold mb-2">Estadísticas del Sistema</h3>
          <p class="text-primary-1 text-sm">Análisis de evaluaciones EPP · Actualizado: {{ $lastUpdated ?: '—' }}</p>
        </div>
        <div class="flex items-center gap-4">
          <button
            wire:click="refresh"
            wire:loading.attr="disabled"
            class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg text-sm font-medium transition-all"
          >
            <x-lucide-refresh-cw class="w-4 h-4" wire:loading.class="animate-spin" wire:target="refresh" />
            <span wire:loading.remove wire:target="refresh">Actualizar</span>
            <span wire:loading wire:target="refresh">Cargando…</span>
          </button>
          <div class="hidden md:block">
            <x-lucide-bar-chart-2 class="w-20 h-20 text-white opacity-20" />
          </div>
        </div>
      </div>
    </div>

    {{-- ── 4 Metric cards ────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

      {{-- Total usuarios --}}
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-primary-1 dark:bg-dark-2 rounded-lg">
            <x-lucide-users class="w-6 h-6 text-primary-5 dark:text-dark-8" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Total</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ $totalUsuarios }}</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Usuarios registrados</p>
      </div>

      {{-- Total evaluaciones --}}
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-accent-400 bg-opacity-10 dark:bg-dark-2 rounded-lg">
            <x-lucide-clipboard-list class="w-6 h-6 text-accent-500 dark:text-dark-8" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">EPP</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ $totalEvals }}</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Evaluaciones realizadas</p>
      </div>

      {{-- Tasa de aprobación --}}
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-success-500 bg-opacity-10 dark:bg-dark-2 rounded-lg">
            <x-lucide-check-circle class="w-6 h-6 text-success-500 dark:text-success-400" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Aprobación</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">{{ $tasaAprobacion }}%</p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Tasa de aprobación global</p>
      </div>

      {{-- Tiempo promedio --}}
      <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md p-6 border border-secondary-200 dark:border-dark-2">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-secondary-100 dark:bg-dark-2 rounded-lg">
            <x-lucide-timer class="w-6 h-6 text-secondary-600 dark:text-dark-8" />
          </div>
          <span class="text-xs font-medium text-secondary-400 dark:text-secondary-300 bg-secondary-100 dark:bg-dark-2 px-2 py-1 rounded">Promedio</span>
        </div>
        <p class="text-3xl font-bold text-secondary-800 dark:text-secondary-100 mb-1">
          @if($tiempoPromedio > 0)
            {{ gmdate('i:s', (int) $tiempoPromedio) }}
          @else
            —
          @endif
        </p>
        <p class="text-sm text-secondary-500 dark:text-secondary-400">Duración promedio (min:seg)</p>
      </div>
    </div>

    {{-- ── Usage stats row ───────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @foreach([
        ['label' => 'Hoy',     'value' => $evalsHoy,    'icon' => 'sun'],
        ['label' => 'Semana',  'value' => $evalsSemana, 'icon' => 'calendar'],
        ['label' => '30 días', 'value' => $evalsMes,    'icon' => 'calendar-days'],
        ['label' => 'Pico hora','value' => $picoUso,    'icon' => 'clock'],
      ] as $stat)
        <div class="bg-white dark:bg-dark-0 rounded-lg border border-secondary-200 dark:border-dark-2 p-4 flex items-center gap-3">
          <div class="flex items-center justify-center w-9 h-9 bg-primary-1 dark:bg-dark-2 rounded-lg flex-shrink-0">
            <x-dynamic-component :component="'lucide-' . $stat['icon']" class="w-4 h-4 text-primary-5 dark:text-dark-8" />
          </div>
          <div class="min-w-0">
            <p class="text-lg font-bold text-secondary-800 dark:text-secondary-100 truncate">{{ $stat['value'] }}</p>
            <p class="text-xs text-secondary-500 dark:text-secondary-400">{{ $stat['label'] }}</p>
          </div>
        </div>
      @endforeach
    </div>

    {{-- ── 30-day bar chart ──────────────────────────────────── --}}
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-1 flex items-center gap-2">
        <x-lucide-bar-chart-2 class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Evaluaciones — Últimos 30 días
      </h4>
      <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-5">Número de evaluaciones EPP completadas por día</p>

      @if(array_sum($chartValues) === 0)
        <div class="text-center py-12 text-secondary-400 dark:text-secondary-500">
          <x-lucide-bar-chart-2 class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p>Sin evaluaciones en los últimos 30 días</p>
        </div>
      @else
        <div class="relative h-64">
          <canvas id="epp-chart"></canvas>
        </div>
      @endif
    </div>

    {{-- ── Instructor comparison table ───────────────────────── --}}
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-1 flex items-center gap-2">
        <x-lucide-trophy class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Comparativa de Instructores
      </h4>
      <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-5">Rendimiento promedio de los grupos por instructor</p>

      @if(empty($instructores))
        <div class="text-center py-10 text-secondary-400 dark:text-secondary-500">
          <x-lucide-users class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p>Sin datos de instructores aún</p>
        </div>
      @else
        <div class="overflow-x-auto rounded-lg border border-secondary-200 dark:border-dark-2">
          <table class="w-full text-sm">
            <thead class="bg-secondary-50 dark:bg-dark-1 border-b border-secondary-200 dark:border-dark-2">
              <tr>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">#</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Instructor</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Aprendices</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Prom. Grupo</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Aprobación</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Consistencia</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Alerta</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-100 dark:divide-dark-2">
              @foreach($instructores as $inst)
                <tr class="hover:bg-secondary-50 dark:hover:bg-dark-1 transition-colors">
                  <td class="px-4 py-3 text-secondary-500 dark:text-secondary-400 font-medium">
                    @if($inst['ranking'] === 1) 🥇
                    @elseif($inst['ranking'] === 2) 🥈
                    @elseif($inst['ranking'] === 3) 🥉
                    @else {{ $inst['ranking'] }}
                    @endif
                  </td>
                  <td class="px-4 py-3 font-medium text-secondary-800 dark:text-secondary-100">{{ $inst['name'] }}</td>
                  <td class="px-4 py-3 text-right text-secondary-600 dark:text-secondary-300">{{ $inst['aprendices'] }}</td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-bold {{ $inst['promedio_grupo'] >= 70 ? 'text-success-600 dark:text-success-400' : 'text-red-500 dark:text-red-400' }}">
                      {{ $inst['promedio_grupo'] }}%
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span class="font-semibold {{ $inst['tasa_aprobacion'] >= 80 ? 'text-success-600 dark:text-success-400' : ($inst['tasa_aprobacion'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-500 dark:text-red-400') }}">
                      {{ $inst['tasa_aprobacion'] }}%
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    @php
                      $cBadge = match($inst['consistencia']) {
                        'alta'  => 'bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-300',
                        'media' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                        default => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                      };
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $cBadge }}">
                      {{ ucfirst($inst['consistencia']) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-xs text-secondary-500 dark:text-secondary-400">
                    @if($inst['alerta'])
                      <span class="flex items-center gap-1 text-yellow-600 dark:text-yellow-400">
                        <x-lucide-alert-triangle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ $inst['alerta'] }}
                      </span>
                    @else
                      <span class="text-success-600 dark:text-success-400 flex items-center gap-1">
                        <x-lucide-check class="w-3.5 h-3.5 flex-shrink-0" />
                        OK
                      </span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    {{-- ── Problematic steps ─────────────────────────────────── --}}
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-1 flex items-center gap-2">
        <x-lucide-alert-triangle class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Análisis por Pasos EPP
      </h4>
      <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-5">Tasa de éxito y variación por paso del protocolo</p>

      @if(empty($steps))
        <div class="text-center py-10 text-secondary-400 dark:text-secondary-500">
          <x-lucide-layers class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p>Sin datos de pasos EPP aún</p>
        </div>
      @else
        <div class="space-y-3">
          @foreach($steps as $step)
            @php
              $barColor  = $step['promedio'] >= 75 ? 'bg-success-500' : ($step['promedio'] >= 50 ? 'bg-yellow-400' : 'bg-red-500');
              $textColor = $step['promedio'] >= 75 ? 'text-success-600 dark:text-success-400' : ($step['promedio'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-500 dark:text-red-400');
            @endphp
            <div class="p-4 rounded-lg border {{ $step['es_critico'] ? 'border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950' : 'border-secondary-100 dark:border-dark-2 bg-secondary-50 dark:bg-dark-1' }}">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <span class="flex items-center justify-center w-7 h-7 bg-primary-5 dark:bg-dark-7 text-white text-xs font-bold rounded-full flex-shrink-0">
                    {{ $step['numero'] }}
                  </span>
                  <span class="font-medium text-secondary-800 dark:text-secondary-100 text-sm">{{ $step['nombre'] }}</span>
                  @if($step['es_critico'])
                    <span class="text-xs px-2 py-0.5 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded font-medium">Paso crítico</span>
                  @endif
                </div>
                <div class="flex items-center gap-4 text-sm">
                  <span class="{{ $textColor }} font-bold">{{ $step['promedio'] }}%</span>
                  <span class="text-secondary-400 dark:text-secondary-500 text-xs">Fallo: {{ $step['tasa_fallo'] }}%</span>
                </div>
              </div>
              {{-- Progress bar --}}
              <div class="h-2 bg-secondary-200 dark:bg-dark-2 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all {{ $barColor }}"
                  style="width: {{ min(100, max(0, $step['promedio'])) }}%"
                ></div>
              </div>
              <div class="flex items-center justify-end mt-1 gap-3">
                @php
                  $vBadge = match($step['variacion']) {
                    'baja'  => 'text-success-600 dark:text-success-400',
                    'media' => 'text-yellow-600 dark:text-yellow-400',
                    default => 'text-red-500 dark:text-red-400',
                  };
                @endphp
                <span class="text-xs {{ $vBadge }}">
                  Variación: {{ ucfirst($step['variacion']) }}
                </span>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>{{-- /space-y-6 --}}

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endassets

@script
<script>
  const labels = @json($chartLabels);
  const values = @json($chartValues);

  if (labels.length) {
    const isDark = () => document.documentElement.classList.contains('dark');

    function buildChart() {
      const ctx = document.getElementById('epp-chart');
      if (!ctx) return;

      const primaryColor = isDark() ? 'rgba(251,146,60,0.85)' : 'rgba(220,38,38,0.80)';
      const gridColor    = isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
      const tickColor    = isDark() ? '#94a3b8' : '#64748b';

      if (window._eppChart) { window._eppChart.destroy(); }

      window._eppChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Evaluaciones',
            data: values,
            backgroundColor: primaryColor,
            borderRadius: 6,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (item) => ` ${item.raw} evaluación${item.raw !== 1 ? 'es' : ''}`,
              }
            }
          },
          scales: {
            x: {
              grid: { color: gridColor },
              ticks: { color: tickColor, maxTicksLimit: 10, font: { size: 11 } },
            },
            y: {
              beginAtZero: true,
              grid: { color: gridColor },
              ticks: { color: tickColor, precision: 0, font: { size: 11 } },
            }
          }
        }
      });
    }

    buildChart();

    new MutationObserver(buildChart)
      .observe(document.documentElement, { attributeFilter: ['class'] });
  }
</script>
@endscript
