<div class="space-y-6">

    {{-- ── Header card ───────────────────────────────────────── --}}
    <div x-data="{ reportModal: false }" class="bg-gradient-to-r from-primary-5 to-primary-6 rounded-xl shadow-lg p-8 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold mb-2">Estadísticas del Sistema</h3>
          <p class="text-primary-1 text-sm">Análisis de evaluaciones EPP · Actualizado: {{ $lastUpdated ?: '—' }}</p>
        </div>
        <div class="flex items-center gap-3">
          {{-- Generar Reporte --}}
          <button
            @click="reportModal = true"
            class="flex items-center gap-2 px-4 py-2 bg-white text-primary-6 hover:bg-primary-1 rounded-lg text-sm font-semibold transition-all shadow"
          >
            <x-lucide-file-down class="w-4 h-4" />
            Generar Reporte
          </button>
          {{-- Actualizar --}}
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

      {{-- ── Modal de Generación de Reporte ── --}}
      <div
        x-show="reportModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;"
      >
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-secondary-900 bg-opacity-60" @click="reportModal = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white dark:bg-dark-0 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10" @click.stop>

          {{-- Cabecera --}}
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-10 h-10 bg-primary-1 dark:bg-dark-2 rounded-lg">
                <x-lucide-file-text class="w-5 h-5 text-primary-5 dark:text-dark-8" />
              </div>
              <div>
                <h3 class="font-bold text-secondary-800 dark:text-secondary-100">Generar Reporte PDF</h3>
                <p class="text-xs text-secondary-500 dark:text-secondary-400">Elige las secciones a incluir</p>
              </div>
            </div>
            <button @click="reportModal = false" class="text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-200 transition-colors">
              <x-lucide-x class="w-5 h-5" />
            </button>
          </div>

          {{-- Formulario de secciones --}}
          <form method="GET" action="{{ route('admin.reports.statistics') }}" @submit="reportModal = false">
            <div class="space-y-3 mb-6">

              <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-dark-2 hover:bg-secondary-50 dark:hover:bg-dark-1 cursor-pointer transition-colors">
                <input type="checkbox" name="sections[]" value="global" checked
                  class="w-4 h-4 rounded accent-primary-5">
                <div class="flex items-center gap-2">
                  <x-lucide-layout-dashboard class="w-4 h-4 text-primary-5 dark:text-dark-8 flex-shrink-0" />
                  <div>
                    <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100">Métricas Globales</p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Usuarios, evaluaciones, tasa de aprobación, tiempos</p>
                  </div>
                </div>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-dark-2 hover:bg-secondary-50 dark:hover:bg-dark-1 cursor-pointer transition-colors">
                <input type="checkbox" name="sections[]" value="chart" checked
                  class="w-4 h-4 rounded accent-primary-5">
                <div class="flex items-center gap-2">
                  <x-lucide-bar-chart-2 class="w-4 h-4 text-primary-5 dark:text-dark-8 flex-shrink-0" />
                  <div>
                    <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100">Actividad — Últimos 30 días</p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Tabla visual de evaluaciones por día</p>
                  </div>
                </div>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-dark-2 hover:bg-secondary-50 dark:hover:bg-dark-1 cursor-pointer transition-colors">
                <input type="checkbox" name="sections[]" value="instructors" checked
                  class="w-4 h-4 rounded accent-primary-5">
                <div class="flex items-center gap-2">
                  <x-lucide-trophy class="w-4 h-4 text-primary-5 dark:text-dark-8 flex-shrink-0" />
                  <div>
                    <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100">Comparativa de Instructores</p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Ranking, promedios y tasas de aprobación</p>
                  </div>
                </div>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-lg border border-secondary-200 dark:border-dark-2 hover:bg-secondary-50 dark:hover:bg-dark-1 cursor-pointer transition-colors">
                <input type="checkbox" name="sections[]" value="steps" checked
                  class="w-4 h-4 rounded accent-primary-5">
                <div class="flex items-center gap-2">
                  <x-lucide-layers class="w-4 h-4 text-primary-5 dark:text-dark-8 flex-shrink-0" />
                  <div>
                    <p class="text-sm font-medium text-secondary-800 dark:text-secondary-100">Análisis de Pasos EPP</p>
                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Tasa de éxito y fallo por cada paso del protocolo</p>
                  </div>
                </div>
              </label>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="reportModal = false"
                class="flex-1 px-4 py-2 border border-secondary-200 dark:border-dark-2 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-dark-1 rounded-lg text-sm font-medium transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-primary-5 hover:bg-primary-6 text-white rounded-lg text-sm font-semibold transition-colors shadow"
              >
                <x-lucide-download class="w-4 h-4" />
                Descargar PDF
              </button>
            </div>
          </form>
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

    {{-- ── Desglose por estado + resumen global ────────────── --}}
    @if($totalEvals > 0)
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-base font-bold text-secondary-800 dark:text-secondary-100 mb-4 flex items-center gap-2">
        <x-lucide-pie-chart class="w-4 h-4 text-primary-5 dark:text-dark-7" />
        Desglose de Evaluaciones
      </h4>

      {{-- Barras de estado --}}
      <div class="space-y-3 mb-5">
        @php
          $estadoItems = [
            ['label' => 'Evaluaciones aprobadas', 'desc' => 'Aprendices que alcanzaron ≥ 70 puntos y completaron todos los pasos', 'count' => $evalsAprobadas,   'colorBar' => 'bg-success-500', 'colorText' => 'text-success-600 dark:text-success-400'],
            ['label' => 'Evaluaciones reprobadas', 'desc' => 'Aprendices que no llegaron al mínimo requerido de 70 puntos',        'count' => $evalsReprobadas,  'colorBar' => 'bg-primary-5',   'colorText' => 'text-primary-5 dark:text-primary-3'],
            ['label' => 'Evaluaciones incompletas','desc' => 'Evaluaciones que se iniciaron pero no se terminaron',                  'count' => $evalsIncompletas, 'colorBar' => 'bg-accent-400',  'colorText' => 'text-accent-500 dark:text-accent-400'],
          ];
        @endphp
        @foreach($estadoItems as $item)
          @php $pct = $totalEvals > 0 ? round($item['count'] / $totalEvals * 100, 1) : 0; @endphp
          <div>
            <div class="flex items-center justify-between mb-1">
              <div>
                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-200">{{ $item['label'] }}</span>
                <span class="ml-2 text-xs text-secondary-400 dark:text-secondary-500">{{ $item['desc'] }}</span>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                <span class="text-sm font-bold {{ $item['colorText'] }}">{{ $item['count'] }}</span>
                <span class="text-xs text-secondary-400 dark:text-secondary-500">({{ $pct }}%)</span>
              </div>
            </div>
            <div class="h-2 bg-secondary-100 dark:bg-dark-2 rounded-full overflow-hidden">
              <div class="h-full rounded-full {{ $item['colorBar'] }}" style="width: {{ $pct }}%"></div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Resumen interpretado --}}
      @php
        $resumenGlobal = null;
        if ($tasaAprobacion >= 80) {
          $resumenGlobal = ['tipo' => 'success', 'texto' => "El sistema muestra un <strong>rendimiento excelente</strong>: {$tasaAprobacion}% de los aprendices aprueba la evaluación EPP. El nivel de preparación general es alto."];
        } elseif ($tasaAprobacion >= 60) {
          $resumenGlobal = ['tipo' => 'warn', 'texto' => "El rendimiento es <strong>aceptable pero mejorable</strong>: {$tasaAprobacion}% de aprobación. Hay margen para reforzar el entrenamiento en los pasos con mayor tasa de fallo."];
        } elseif ($tasaAprobacion >= 40) {
          $resumenGlobal = ['tipo' => 'warn', 'texto' => "El rendimiento está <strong>por debajo de lo esperado</strong>: solo el {$tasaAprobacion}% aprueba. Se recomienda revisar el plan de entrenamiento con los instructores."];
        } else {
          $resumenGlobal = ['tipo' => 'danger', 'texto' => "El rendimiento es <strong>crítico</strong>: solo el {$tasaAprobacion}% de los aprendices aprueba. Se requiere una revisión urgente del proceso de entrenamiento EPP."];
        }
        if ($evalsIncompletas > 0 && $totalEvals > 0) {
          $pctInc = round($evalsIncompletas / $totalEvals * 100, 1);
          if ($pctInc >= 20) {
            $resumenGlobal['texto'] .= " Además, el <strong>{$pctInc}%</strong> de las evaluaciones quedó incompleto, lo que puede indicar problemas técnicos o abandono durante la prueba.";
          }
        }
      @endphp
      @if($resumenGlobal)
        @php
          $rStyle = match($resumenGlobal['tipo']) {
            'success' => 'border-success-400 dark:border-success-600 bg-success-200 dark:bg-dark-1 text-success-700 dark:text-success-400',
            'warn'    => 'border-accent-400 dark:border-accent-600 bg-accent-200 dark:bg-dark-1 text-accent-600 dark:text-accent-400',
            default   => 'border-primary-3 dark:border-dark-3 bg-primary-1 dark:bg-dark-1 text-primary-6 dark:text-primary-3',
          };
          $rIcon = match($resumenGlobal['tipo']) {
            'success' => 'check-circle',
            'warn'    => 'alert-circle',
            default   => 'alert-triangle',
          };
        @endphp
        <div class="flex items-start gap-3 p-4 rounded-xl border {{ $rStyle }}">
          <x-dynamic-component :component="'lucide-' . $rIcon" class="w-5 h-5 flex-shrink-0 mt-0.5" />
          <p class="text-sm">{!! $resumenGlobal['texto'] !!}</p>
        </div>
      @endif
    </div>
    @endif

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
      <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">¿Cómo le está yendo a cada instructor con sus aprendices?</p>
      <div class="flex flex-wrap gap-x-6 gap-y-1 mb-5 text-xs text-secondary-400 dark:text-secondary-500">
        <span class="flex items-center gap-1">
          <x-lucide-bar-chart-2 class="w-3.5 h-3.5 flex-shrink-0" />
          <strong>Promedio del grupo:</strong> calificación media de todos sus aprendices (0–100)
        </span>
        <span class="flex items-center gap-1">
          <x-lucide-check-circle class="w-3.5 h-3.5 flex-shrink-0 text-success-500" />
          <strong>% aprobados:</strong> de cada 100 aprendices, cuántos superaron la evaluación EPP
        </span>
        <span class="flex items-center gap-1">
          <x-lucide-users class="w-3.5 h-3.5 flex-shrink-0" />
          <strong>Uniformidad:</strong> qué tan parejos son los resultados del grupo — Alta = todos aprenden de forma similar · Baja = hay grandes diferencias entre aprendices, algunos pueden estar quedando atrás
        </span>
      </div>

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
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Posición según el promedio del grupo (mayor = mejor)">#</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300">Instructor</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Cantidad de aprendices que tiene a cargo">Aprendices</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Calificación media de todos sus aprendices, de 0 a 100">Prom. grupo</th>
                <th class="text-right px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Porcentaje de aprendices que aprobaron la evaluación EPP">% aprobados</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Qué tan parejos son los resultados del grupo. Alta = todos aprenden de forma similar. Baja = hay grandes diferencias entre aprendices.">Uniformidad</th>
                <th class="text-left px-4 py-3 font-semibold text-secondary-600 dark:text-secondary-300" title="Indica si hay algo que revisar en este grupo">Estado</th>
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
                    <span
                      class="font-bold {{ $inst['promedio_grupo'] >= 70 ? 'text-success-600 dark:text-success-400' : 'text-primary-5 dark:text-primary-3' }}"
                      title="{{ $inst['promedio_grupo'] >= 70 ? 'Buen rendimiento del grupo' : 'El grupo necesita mejorar (mínimo recomendado: 70)' }}"
                    >{{ $inst['promedio_grupo'] }} / 100</span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <span
                      class="font-semibold {{ $inst['tasa_aprobacion'] >= 80 ? 'text-success-600 dark:text-success-400' : ($inst['tasa_aprobacion'] >= 50 ? 'text-accent-500 dark:text-accent-400' : 'text-primary-5 dark:text-primary-3') }}"
                      title="Para aprobar se necesita ≥ 70 puntos en la evaluación. {{ $inst['tasa_aprobacion'] >= 80 ? 'Alta aprobación' : ($inst['tasa_aprobacion'] >= 50 ? 'Aprobación moderada' : ($inst['tasa_aprobacion'] === 0.0 ? 'Ningún aprendiz alcanzó los 70 puntos requeridos' : 'Baja aprobación — requiere atención')) }}"
                    >{{ $inst['tasa_aprobacion'] }}%</span>
                  </td>
                  <td class="px-4 py-3">
                    @php
                      $cBadge = match($inst['consistencia']) {
                        'alta'  => 'bg-success-200 dark:bg-dark-2 text-success-600 dark:text-success-400',
                        'media' => 'bg-accent-200 dark:bg-dark-2 text-accent-500 dark:text-accent-400',
                        default => 'bg-primary-1 dark:bg-dark-2 text-primary-6 dark:text-primary-3',
                      };
                      $cLabel = match($inst['consistencia']) {
                        'alta'  => 'Alta — todos los aprendices obtienen resultados similares',
                        'media' => 'Media — algunos aprendices difieren del resto',
                        default => 'Baja — hay grandes diferencias entre aprendices del grupo',
                      };
                      $cText = match($inst['consistencia']) {
                        'alta'  => 'Grupo uniforme',
                        'media' => 'Algo variado',
                        default => 'Muy variado',
                      };
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $cBadge }}" title="{{ $cLabel }}">
                      {{ $cText }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-xs text-secondary-500 dark:text-secondary-400">
                    @if($inst['alerta'])
                      <span class="flex items-center gap-1 text-accent-500 dark:text-accent-400" title="{{ $inst['alerta'] }}">
                        <x-lucide-alert-triangle class="w-3.5 h-3.5 flex-shrink-0" />
                        Revisar grupo
                      </span>
                    @else
                      <span class="text-success-600 dark:text-success-400 flex items-center gap-1" title="El grupo no presenta alertas">
                        <x-lucide-check class="w-3.5 h-3.5 flex-shrink-0" />
                        Sin alertas
                      </span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- ── Resumen instructores ── --}}
        @php
          $mejorInst = collect($instructores)->first();
          $peorInst  = collect($instructores)->last();
        @endphp
        @if($mejorInst && count($instructores) > 1)
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

          {{-- Mejor instructor --}}
          <div class="flex items-start gap-3 p-4 rounded-xl border border-success-400 dark:border-success-600 bg-success-200 dark:bg-dark-1">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-success-500 bg-opacity-20 flex-shrink-0">
              <x-lucide-trophy class="w-5 h-5 text-success-600 dark:text-success-400" />
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-success-600 dark:text-success-400 uppercase tracking-wide mb-0.5">Mejor grupo del período</p>
              <p class="font-bold text-secondary-800 dark:text-secondary-100 truncate">{{ $mejorInst['name'] }}</p>
              <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                Promedio <strong>{{ $mejorInst['promedio_grupo'] }}/100</strong>
                &nbsp;·&nbsp;
                <strong>{{ $mejorInst['tasa_aprobacion'] }}%</strong> de sus aprendices aprobaron
                &nbsp;·&nbsp;
                {{ $mejorInst['aprendices'] }} aprendice{{ $mejorInst['aprendices'] !== 1 ? 's' : '' }}
              </p>
            </div>
          </div>

          {{-- Grupo a reforzar --}}
          <div class="flex items-start gap-3 p-4 rounded-xl border border-primary-3 dark:border-dark-3 bg-primary-1 dark:bg-dark-1">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-5 bg-opacity-10 flex-shrink-0">
              <x-lucide-alert-triangle class="w-5 h-5 text-primary-5 dark:text-primary-3" />
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-primary-5 dark:text-primary-3 uppercase tracking-wide mb-0.5">Grupo que más necesita apoyo</p>
              <p class="font-bold text-secondary-800 dark:text-secondary-100 truncate">{{ $peorInst['name'] }}</p>
              <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                Promedio <strong>{{ $peorInst['promedio_grupo'] }}/100</strong>
                &nbsp;·&nbsp;
                <strong>{{ $peorInst['tasa_aprobacion'] }}%</strong> de aprobación
                @if($peorInst['alerta'])
                  &nbsp;·&nbsp;
                  <span class="text-primary-5 dark:text-primary-3">{{ $peorInst['alerta'] }}</span>
                @endif
              </p>
            </div>
          </div>

        </div>
        @endif
      @endif
    </div>

    {{-- ── Problematic steps ─────────────────────────────────── --}}
    <div class="bg-white dark:bg-dark-0 rounded-xl shadow-md border border-secondary-200 dark:border-dark-2 p-6">
      <h4 class="text-lg font-bold text-secondary-800 dark:text-secondary-100 mb-1 flex items-center gap-2">
        <x-lucide-alert-triangle class="w-5 h-5 text-primary-5 dark:text-dark-7" />
        Análisis por Pasos EPP
      </h4>
      <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">¿Qué tan bien realizan los aprendices cada paso de colocación del EPP?</p>
      <div class="flex flex-wrap gap-x-6 gap-y-1 mb-5 text-xs text-secondary-400 dark:text-secondary-500">
        <span class="flex items-center gap-1">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-success-500"></span>
          <strong>% de éxito:</strong> porcentaje de aprendices que completaron el paso correctamente
        </span>
        <span class="flex items-center gap-1">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-accent-400"></span>
          <strong>Regularidad:</strong> qué tan similares son los resultados entre aprendices — Baja = todos lo hacen parecido · Alta = hay diferencias marcadas entre unos y otros
        </span>
        <span class="flex items-center gap-1">
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary-3"></span>
          <strong>Paso crítico:</strong> el paso donde los aprendices obtienen el resultado más bajo en promedio
        </span>
      </div>

      @if(empty($steps))
        <div class="text-center py-10 text-secondary-400 dark:text-secondary-500">
          <x-lucide-layers class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p>Sin datos de pasos EPP aún</p>
        </div>
      @else
        <div class="space-y-3">
          @foreach($steps as $step)
            @php
              $barColor  = $step['promedio'] >= 75 ? 'bg-success-500' : ($step['promedio'] >= 50 ? 'bg-accent-400' : 'bg-primary-3');
              $textColor = $step['promedio'] >= 75 ? 'text-success-600 dark:text-success-400' : ($step['promedio'] >= 50 ? 'text-accent-400 dark:text-accent-400' : 'text-primary-5 dark:text-primary-3');
            @endphp
            <div class="p-4 rounded-lg border {{ $step['es_critico'] ? 'border-primary-2 dark:border-dark-3 bg-primary-1 dark:bg-dark-1' : 'border-secondary-100 dark:border-dark-2 bg-secondary-50 dark:bg-dark-1' }}">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="flex items-center justify-center w-7 h-7 bg-primary-5 dark:bg-dark-7 text-white text-xs font-bold rounded-full flex-shrink-0">
                    {{ $step['numero'] }}
                  </span>
                  <span class="font-medium text-secondary-800 dark:text-secondary-100 text-sm">{{ $step['nombre'] }}</span>
                  @if($step['es_critico'])
                    <span title="Este es el paso con el resultado promedio más bajo de todos" class="text-xs px-2 py-0.5 bg-primary-1 dark:bg-dark-2 text-primary-6 dark:text-primary-3 rounded font-medium cursor-help">Paso más difícil</span>
                  @endif
                </div>
                <div class="flex items-center gap-4 text-sm">
                  <span class="{{ $textColor }} font-bold" title="Porcentaje de aprendices que realizaron este paso correctamente">{{ $step['promedio'] }}% de éxito</span>
                  <span class="text-secondary-500 dark:text-secondary-400 text-xs" title="Porcentaje de aprendices que fallaron este paso">{{ $step['tasa_fallo'] }}% de fallo</span>
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
                    'baja'  => 'text-secondary-700 dark:text-secondary-400',
                    'media' => 'text-secondary-700 dark:text-secondary-400',
                    default => 'text-secondary-700 dark:text-secondary-400',
                  };
                  $vLabel = match($step['variacion']) {
                    'baja'  => 'Baja — resultados similares entre aprendices',
                    'media' => 'Media — algunas diferencias entre aprendices',
                    default => 'Alta — resultados muy distintos entre aprendices',
                  };
                @endphp
                <span class="text-xs {{ $vBadge }}" title="{{ $vLabel }}">
                  Regularidad: <span class="font-medium">{{ ucfirst($step['variacion']) }}</span>
                </span>
              </div>
            </div>
          @endforeach
        </div>

        {{-- ── Resumen pasos EPP ── --}}
        @php
          $stepsCol  = collect($steps);
          $mejorPaso = $stepsCol->sortByDesc('promedio')->first();
          $peorPaso  = $stepsCol->sortBy('promedio')->first();
        @endphp
        @if($mejorPaso && $stepsCol->count() > 1)
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

          {{-- Paso más dominado --}}
          <div class="flex items-start gap-3 p-4 rounded-xl border border-success-400 dark:border-success-600 bg-success-200 dark:bg-dark-1">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-success-500 bg-opacity-20 flex-shrink-0">
              <x-lucide-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-success-600 dark:text-success-400 uppercase tracking-wide mb-0.5">Paso que mejor dominan</p>
              <p class="font-bold text-secondary-800 dark:text-secondary-100">
                Paso {{ $mejorPaso['numero'] }} — {{ $mejorPaso['nombre'] }}
              </p>
              <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                <strong>{{ $mejorPaso['promedio'] }}%</strong> de los aprendices lo realizan correctamente
                &nbsp;·&nbsp;
                Solo <strong>{{ $mejorPaso['tasa_fallo'] }}%</strong> falla en este paso
              </p>
            </div>
          </div>

          {{-- Paso más difícil --}}
          <div class="flex items-start gap-3 p-4 rounded-xl border border-primary-3 dark:border-dark-3 bg-primary-1 dark:bg-dark-1">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-5 bg-opacity-10 flex-shrink-0">
              <x-lucide-alert-triangle class="w-5 h-5 text-primary-5 dark:text-primary-3" />
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-primary-5 dark:text-primary-3 uppercase tracking-wide mb-0.5">Paso que más les cuesta</p>
              <p class="font-bold text-secondary-800 dark:text-secondary-100">
                Paso {{ $peorPaso['numero'] }} — {{ $peorPaso['nombre'] }}
              </p>
              <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                Solo <strong>{{ $peorPaso['promedio'] }}%</strong> lo realiza correctamente
                &nbsp;·&nbsp;
                <strong>{{ $peorPaso['tasa_fallo'] }}%</strong> de los aprendices falla aquí — se recomienda reforzar este paso
              </p>
            </div>
          </div>

        </div>
        @endif
      @endif
    </div>

  </div>{{-- /space-y-6 --}}

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endassets

@script
<script>
  const isDark = () => document.documentElement.classList.contains('dark');

  function buildChart(labels, values) {
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

  // Rendderizar al cargar por primera vez con los datos iniciales
  buildChart(@json($chartLabels), @json($chartValues));

  // Al refrescar, Livewire envía los datos nuevos en el payload del evento
  $wire.on('stats-refreshed', ({ chartLabels, chartValues }) => {
    buildChart(chartLabels, chartValues);
  });

  // Reconstruir al cambiar entre dark/light mode
  new MutationObserver(() => { if (window._eppChart) buildChart(window._eppChart.data.labels, window._eppChart.data.datasets[0].data); })
    .observe(document.documentElement, { attributeFilter: ['class'] });
</script>
@endscript
