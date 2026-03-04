<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    @page { margin: 20mm 18mm 22mm 18mm; }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      color: #1e293b;
      background: #fff;
      line-height: 1.5;
    }

    /* ── Header ── */
    .header {
      background: #dc2626;
      color: #fff;
      padding: 0;
      margin-bottom: 22px;
      border-radius: 6px;
      overflow: hidden;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
    }
    .header-logo-cell {
      width: 90px;
      background: #fff;
      padding: 10px 12px;
      vertical-align: middle;
      border-right: 4px solid #b91c1c;
    }
    .header-title-cell {
      padding: 14px 18px;
      vertical-align: middle;
    }
    .header h1 { font-size: 19px; font-weight: 700; margin-bottom: 2px; color: #fff; }
    .header p  { font-size: 9px; opacity: 0.85; color: #fff; }
    .header-meta { text-align: right; font-size: 9px; opacity: 0.90; vertical-align: top; padding: 14px 16px; }

    /* ── Section title ── */
    .section {
      margin-bottom: 22px;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      overflow: hidden;
    }
    .section-title {
      font-size: 11px;
      font-weight: 700;
      color: #fff;
      background: #dc2626;
      padding: 7px 14px;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
    }
    .section-body { padding: 0 14px 14px; }

    /* ── Metric cards (2×2 grid via table) ── */
    .cards-table { width: 100%; border-collapse: separate; border-spacing: 8px; }
    .card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 10px 12px;
      vertical-align: top;
      width: 25%;
    }
    .card-label { font-size: 9px; color: #64748b; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
    .card-value { font-size: 18px; font-weight: 700; color: #1e293b; }
    .card-sub   { font-size: 9px; color: #94a3b8; margin-top: 2px; }

    /* ── Stat rows ── */
    .stat-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f1f5f9; }
    .stat-label { color: #475569; }
    .stat-value { font-weight: 600; color: #1e293b; }

    /* ── Tables ── */
    table.data {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
    }
    table.data th {
      background: #f1f5f9;
      padding: 7px 10px;
      text-align: left;
      font-weight: 700;
      color: #475569;
      border: 1px solid #e2e8f0;
      text-transform: uppercase;
      font-size: 9px;
      letter-spacing: 0.4px;
    }
    table.data td {
      padding: 7px 10px;
      border: 1px solid #e2e8f0;
      color: #334155;
    }
    table.data tr:nth-child(even) td { background: #f8fafc; }
    .td-right { text-align: right; }
    .td-center { text-align: center; }

    /* ── Badge ── */
    .badge {
      display: inline-block;
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 9px;
      font-weight: 600;
    }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-red    { background: #fee2e2; color: #991b1b; }

    /* ── Column chart (vertical bars) ── */
    .col-chart-wrap { width: 100%; }
    .col-chart-table { width: 100%; border-collapse: collapse; border-spacing: 0; }
    .col-chart-labels { width: 100%; border-collapse: collapse; border-spacing: 0; border-top: 1px solid #e2e8f0; }
    .col-bar-cell { padding: 0 1px; vertical-align: bottom; }
    .col-bar-empty { padding: 0; }
    .col-bar-fill  { padding: 0; background: #dc2626; border-radius: 2px 2px 0 0; }
    .col-label-cell { font-size: 7px; color: #64748b; text-align: center; padding-top: 3px; }

    /* ── Step progress bar ── */
    .step-row { margin-bottom: 8px; }
    .step-header { display: flex; justify-content: space-between; margin-bottom: 3px; }
    .step-name  { font-size: 10px; color: #334155; }
    .step-pct   { font-size: 10px; font-weight: 700; }
    .progress-track { width: 100%; height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden; }
    .progress-fill  { height: 100%; border-radius: 5px; }
    .pg-green  { background: #22c55e; }
    .pg-yellow { background: #eab308; }
    .pg-red    { background: #ef4444; }

    /* ── Footer ── */
    .footer {
      position: fixed;
      bottom: -16mm;
      left: -18mm; right: -18mm;
      text-align: center;
      font-size: 8px;
      color: #94a3b8;
      padding: 7px 24px;
      border-top: 2px solid #dc2626;
      background: #fff;
    }

    .text-green  { color: #16a34a; }
    .text-yellow { color: #ca8a04; }
    .text-red    { color: #dc2626; }
    .fw-bold { font-weight: 700; }
    .mt-4 { margin-top: 4px; }
    .mb-4 { margin-bottom: 4px; }
  </style>
</head>
<body>

  {{-- ── Header ── --}}
  <div class="header">
    <table class="header-table">
      <tr>
        {{-- Logo --}}
        <td class="header-logo-cell">
          @if(!empty($logo_b64))
            <img src="{{ $logo_b64 }}" style="width:66px;height:auto;display:block;margin:0 auto;">
          @else
            <div style="width:66px;height:50px;background:#fee2e2;border-radius:4px;"></div>
          @endif
        </td>
        {{-- Título --}}
        <td class="header-title-cell">
          <h1>Reporte de Estadísticas EPP</h1>
          <p>Cuerpo de Bomberos &middot; Sistema de Apoyo al Entrenamiento</p>
          <div style="margin-top:6px;">
            @foreach(['global' => 'Métricas Globales', 'chart' => 'Actividad 30 días', 'instructors' => 'Instructores', 'steps' => 'Pasos EPP'] as $key => $label)
              @if(in_array($key, $sections))
                <span style="background:rgba(255,255,255,0.20);border:1px solid rgba(255,255,255,0.40);padding:2px 8px;border-radius:8px;font-size:8px;margin-right:3px;color:#fff;">{{ $label }}</span>
              @endif
            @endforeach
          </div>
        </td>
        {{-- Meta --}}
        <td class="header-meta">
          <div style="font-size:8px;opacity:0.7;margin-bottom:2px;text-transform:uppercase;letter-spacing:0.4px;">Generado por</div>
          <div style="font-weight:700;font-size:10px;">{{ $generated_by }}</div>
          <div style="margin-top:4px;font-size:9px;">{{ $generated_at }}</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- Footer fijo en todas las páginas --}}
  <div class="footer">
    Reporte confidencial — Cuerpo de Bomberos · Generado el {{ $generated_at }} · Página <span class="pagenum"></span>
  </div>

  {{-- ── 1. Métricas Globales ── --}}
  @if(in_array('global', $sections) && isset($global))
    <div class="section">
      <div class="section-title">Métricas Globales</div>
      <div class="section-body">

      {{-- 4 tarjetas principales --}}
      <table class="cards-table" style="margin-bottom:10px;">
        <tr>
          <td class="card">
            <div class="card-label">Total Usuarios</div>
            <div class="card-value">{{ $global['total_usuarios'] }}</div>
            <div class="card-sub">{{ $global['instructores'] }} instructores · {{ $global['aprendices'] }} aprendices</div>
          </td>
          <td class="card">
            <div class="card-label">Evaluaciones Totales</div>
            <div class="card-value">{{ $global['total_evals'] }}</div>
            <div class="card-sub">{{ $global['evals_mes'] }} en los últimos 30 días</div>
          </td>
          <td class="card">
            <div class="card-label">Tasa de Aprobación</div>
            <div class="card-value {{ $global['tasa_aprobacion'] >= 70 ? 'text-green' : ($global['tasa_aprobacion'] >= 50 ? 'text-yellow' : 'text-red') }}">
              {{ $global['tasa_aprobacion'] }}%
            </div>
            <div class="card-sub">{{ $global['aprobadas'] }} aprobadas / {{ $global['total_evals'] }} total</div>
          </td>
          <td class="card">
            <div class="card-label">Tiempo Promedio</div>
            <div class="card-value">
              @if($global['tiempo_promedio'] > 0)
                {{ gmdate('i:s', (int) $global['tiempo_promedio']) }}
              @else
                —
              @endif
            </div>
            <div class="card-sub">min:seg por evaluación</div>
          </td>
        </tr>
      </table>

      {{-- Desglose por estado y uso --}}
      <table style="width:100%;border-collapse:separate;border-spacing:8px;">
        <tr>
          <td style="width:50%;vertical-align:top;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px;">
            <div style="font-size:10px;font-weight:700;color:#475569;margin-bottom:6px;">Desglose por Estado</div>
            <div class="stat-row">
              <span class="stat-label">✔ Aprobadas <span style="font-size:8px;color:#94a3b8;">(aprendices que alcanzaron ≥70 puntos)</span></span>
              <span class="stat-value text-green">{{ $global['aprobadas'] }} <span style="font-weight:400;font-size:9px;">({{ $global['total_evals'] > 0 ? round($global['aprobadas']/$global['total_evals']*100,1) : 0 }}%)</span></span>
            </div>
            <div class="stat-row">
              <span class="stat-label">✖ Reprobadas <span style="font-size:8px;color:#94a3b8;">(no llegaron al mínimo de 70 puntos)</span></span>
              <span class="stat-value text-red">{{ $global['reprobadas'] }} <span style="font-weight:400;font-size:9px;">({{ $global['total_evals'] > 0 ? round($global['reprobadas']/$global['total_evals']*100,1) : 0 }}%)</span></span>
            </div>
            <div class="stat-row" style="border:0">
              <span class="stat-label">○ Incompletas <span style="font-size:8px;color:#94a3b8;">(iniciadas pero no terminadas)</span></span>
              <span class="stat-value text-yellow">{{ $global['incompletas'] }} <span style="font-weight:400;font-size:9px;">({{ $global['total_evals'] > 0 ? round($global['incompletas']/$global['total_evals']*100,1) : 0 }}%)</span></span>
            </div>
          </td>
          <td style="width:50%;vertical-align:top;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px;">
            <div style="font-size:10px;font-weight:700;color:#475569;margin-bottom:6px;">Uso del Sistema</div>
            <div class="stat-row"><span class="stat-label">Hoy </span><span class="stat-value">{{ $global['evals_hoy'] }} evaluaciones</span></div>
            <div class="stat-row"><span class="stat-label">Esta semana </span><span class="stat-value">{{ $global['evals_semana'] }} evaluaciones</span></div>
            <div class="stat-row" style="border:0"><span class="stat-label">Último mes </span><span class="stat-value">{{ $global['evals_mes'] }} evaluaciones</span></div>
          </td>
        </tr>
      </table>

      {{-- Resumen interpretado --}}
      @php
        $tasa = $global['tasa_aprobacion'];
        if ($tasa >= 80)       $resMsg = "El sistema muestra un rendimiento excelente: {$tasa}% de aprobación. El nivel de preparación general es alto.";
        elseif ($tasa >= 60)   $resMsg = "El rendimiento es aceptable pero mejorable: {$tasa}% de aprobación. Hay margen para reforzar el entrenamiento.";
        elseif ($tasa >= 40)   $resMsg = "El rendimiento está por debajo de lo esperado: solo el {$tasa}% aprueba. Se recomienda revisar el plan de entrenamiento con los instructores.";
        else                   $resMsg = "El rendimiento es crítico: solo el {$tasa}% aprueba. Se requiere una revisión urgente del proceso de entrenamiento EPP.";
        $pctInc = $global['total_evals'] > 0 ? round($global['incompletas'] / $global['total_evals'] * 100, 1) : 0;
        if ($pctInc >= 20) $resMsg .= " Además, el {$pctInc}% de las evaluaciones quedó incompleto, lo que puede indicar abandono o problemas técnicos durante la prueba.";
      @endphp
      <div style="margin-top:10px;border:1px solid #cbd5e1;border-left:3px solid #64748b;border-radius:4px;padding:10px 14px;background:#f8fafc;">
        <p style="margin:0 0 2px;font-size:9.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.4px;">Resumen del período</p>
        <p style="margin:0;font-size:9px;color:#334155;">{{ $resMsg }}</p>
      </div>
      </div>{{-- /section-body --}}
    </div>
  @endif

  {{-- ── 2. Actividad últimos 30 días ── --}}
  @if(in_array('chart', $sections) && isset($chart, $chartSummary))
    <div class="section">
      <div class="section-title">Actividad — Últimos 30 Días</div>
      <div class="section-body">

      {{-- 4 tarjetas resumen --}}
      <table class="cards-table" style="margin-bottom:12px;">
        <tr>
          <td class="card">
            <div class="card-label">Total Evaluaciones</div>
            <div class="card-value">{{ $chartSummary['total'] }}</div>
            <div class="card-sub">en los últimos 30 días</div>
          </td>
          <td class="card">
            <div class="card-label">Promedio Diario</div>
            <div class="card-value">{{ $chartSummary['promedio_diario'] }}</div>
            <div class="card-sub">evaluaciones/día</div>
          </td>
          <td class="card">
            <div class="card-label">Día más Activo</div>
            <div class="card-value">{{ $chartSummary['max_dia_label'] }}</div>
            <div class="card-sub">{{ $chartSummary['max_val'] }} evaluaciones</div>
          </td>
          <td class="card">
            <div class="card-label">Días con Actividad</div>
            <div class="card-value {{ $chartSummary['dias_activos'] > 0 ? 'text-green' : 'text-red' }}">{{ $chartSummary['dias_activos'] }}</div>
            <div class="card-sub">{{ $chartSummary['dias_sin'] }} días sin actividad</div>
          </td>
        </tr>
      </table>

      {{-- Gráfico de columnas verticales (30 días) --}}
      <div style="font-size:9px;font-weight:700;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.4px;">
        Actividad Diaria
      </div>
      @php $barMaxH = 90; @endphp
      <div class="col-chart-wrap">
        {{-- Fila de barras --}}
        <table class="col-chart-table">
          <tr>
            @foreach($chart as $point)
              @php
                $barH   = $chartMax > 0 ? (int) round($point['value'] / $chartMax * $barMaxH) : 0;
                $emptyH = $barMaxH - $barH;
                $isMax  = $point['value'] === $chartSummary['max_val'] && $point['value'] > 0;
                $barColor = $isMax ? '#b91c1c' : ($point['value'] > 0 ? '#dc2626' : 'transparent');
              @endphp
              <td class="col-bar-cell" style="width:{{ round(100/30, 3) }}%;">
                <table style="width:100%;border-collapse:collapse;border-spacing:0;">
                  <tr><td class="col-bar-empty" style="height:{{ $emptyH }}px;"></td></tr>
                  <tr><td class="col-bar-fill"  style="height:{{ max(1, $barH) }}px;background:{{ $barColor }};border-radius:2px 2px 0 0;"></td></tr>
                </table>
              </td>
            @endforeach
          </tr>
        </table>
        {{-- Línea base + etiquetas (cada 5 días para no saturar) --}}
        <table class="col-chart-labels">
          <tr>
            @foreach($chart as $loopIndex => $point)
              <td class="col-label-cell" style="width:{{ round(100/30, 3) }}%;">
                {{ ($loopIndex % 5 === 0) ? $point['label'] : '' }}
              </td>
            @endforeach
          </tr>
        </table>
      </div>

      {{-- Tabla de desglose semanal --}}
      @if(!empty($chartWeeks))
        <div style="font-size:9px;font-weight:700;color:#475569;margin:10px 0 5px;text-transform:uppercase;letter-spacing:0.4px;">
          Resumen Semanal
        </div>
        <table class="data">
          <thead>
            <tr>
              <th>Período</th>
              <th class="td-right">Total</th>
              <th class="td-right">Máx. diario</th>
              <th class="td-right">Promedio/día</th>
              <th class="td-center">Actividad</th>
            </tr>
          </thead>
          <tbody>
            @foreach($chartWeeks as $i => $week)
              @php
                $semLabel = 'Sem. ' . ($i + 1) . ' (' . $week['desde'] . ' – ' . $week['hasta'] . ')';
                $actClass = $week['total'] >= 10 ? 'badge-green' : ($week['total'] >= 3 ? 'badge-yellow' : 'badge-red');
                $actText  = $week['total'] >= 10 ? 'Alta' : ($week['total'] >= 3 ? 'Media' : 'Baja');
              @endphp
              <tr>
                <td>{{ $semLabel }}</td>
                <td class="td-right fw-bold">{{ $week['total'] }}</td>
                <td class="td-right">{{ $week['max'] }}</td>
                <td class="td-right">{{ $week['prom'] }}</td>
                <td class="td-center"><span class="badge {{ $actClass }}">{{ $actText }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

      <div style="font-size:9px;color:#94a3b8;margin-top:6px;text-align:right;">
        Período: últimos 30 días · Total: {{ $chartSummary['total'] }} evaluaciones · Máximo diario: {{ $chartSummary['max_val'] }}
      </div>
      </div>{{-- /section-body --}}
    </div>
  @endif

  {{-- ── 3. Comparativa de Instructores ── --}}
  @if(in_array('instructors', $sections) && isset($instructors))
    <div class="section">
      <div class="section-title">Comparativa de Instructores</div>
      <div class="section-body">
      @if(empty($instructors))
        <p style="color:#94a3b8;font-style:italic;">Sin datos de instructores disponibles.</p>
      @else
        <table class="data">
          <thead>
            <tr>
              <th>#</th>
              <th>Instructor</th>
              <th class="td-right">Aprendices</th>
              <th class="td-right">Prom. Grupo</th>
              <th class="td-right">Aprobación</th>
              <th class="td-center">Consistencia</th>
            </tr>
          </thead>
          <tbody>
            @foreach($instructors as $inst)
              <tr>
                <td class="td-center fw-bold">{{ $inst['ranking'] }}</td>
                <td>{{ $inst['name'] }}</td>
                <td class="td-right">{{ $inst['aprendices'] }}</td>
                <td class="td-right">
                  <span class="{{ $inst['promedio_grupo'] >= 70 ? 'text-green' : 'text-red' }} fw-bold">
                    {{ $inst['promedio_grupo'] }}%
                  </span>
                </td>
                <td class="td-right">
                  <span class="{{ $inst['tasa_aprobacion'] >= 80 ? 'text-green' : ($inst['tasa_aprobacion'] >= 50 ? 'text-yellow' : 'text-red') }} fw-bold">
                    {{ $inst['tasa_aprobacion'] }}%
                  </span>
                </td>
                <td class="td-center">
                  @php
                    $bc = match($inst['consistencia']) { 'alta' => 'badge-green', 'media' => 'badge-yellow', default => 'badge-red' };
                  @endphp
                  <span class="badge {{ $bc }}">{{ ucfirst($inst['consistencia']) }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        @php
          $instCol   = collect($instructors);
          $mejorInst = $instCol->first();
          $peorInst  = $instCol->last();
        @endphp
        @if($mejorInst && $instCol->count() > 1)
        <div style="margin-top:12px;border:1px solid #cbd5e1;border-left:3px solid #64748b;border-radius:4px;padding:10px 14px;background:#f8fafc;">
          <p style="margin:0 0 4px;font-size:9.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.4px;">Resumen del período</p>
          <p style="margin:0 0 5px;font-size:9px;color:#334155;">
            <strong>Mejor grupo:</strong> {{ $mejorInst['name'] }} &mdash;
            promedio de <strong>{{ $mejorInst['promedio_grupo'] }}/100</strong>,
            con un <strong>{{ $mejorInst['tasa_aprobacion'] }}%</strong> de aprendices aprobados
            ({{ $mejorInst['aprendices'] }} aprendice{{ $mejorInst['aprendices'] !== 1 ? 's' : '' }}).
          </p>
          <p style="margin:0;font-size:9px;color:#334155;">
            <strong>Grupo que más necesita apoyo:</strong> {{ $peorInst['name'] }} &mdash;
            promedio de <strong>{{ $peorInst['promedio_grupo'] }}/100</strong>,
            <strong>{{ $peorInst['tasa_aprobacion'] }}%</strong> de aprobación.
            @if($peorInst['alerta'] ?? null)
              {{ $peorInst['alerta'] }}.
            @endif
          </p>
        </div>
        @endif
      @endif
      </div>{{-- /section-body --}}
    </div>
  @endif

  {{-- ── 4. Análisis de Pasos EPP ── --}}
  @if(in_array('steps', $sections) && isset($steps))
    <div class="section">
      <div class="section-title">Análisis por Pasos EPP</div>
      <div class="section-body">
      @if(empty($steps))
        <p style="color:#94a3b8;font-style:italic;">Sin datos de pasos disponibles.</p>
      @else
        @foreach($steps as $step)
          @php
            $pct      = $step['promedio'];
            $pgClass  = $pct >= 75 ? 'pg-green' : ($pct >= 50 ? 'pg-yellow' : 'pg-red');
            $txtClass = $pct >= 75 ? 'text-green' : ($pct >= 50 ? 'text-yellow' : 'text-red');
          @endphp
          <div class="step-row">
            <div class="step-header">
              <span class="step-name">
                <span style="display:inline-block;width:18px;height:18px;background:#dc2626;color:#fff;border-radius:50%;text-align:center;line-height:18px;font-size:9px;font-weight:700;margin-right:5px;">{{ $step['numero'] }}</span>
                {{ $step['nombre'] }}
                @if($step['es_critico'])
                  <span class="badge badge-red" style="margin-left:4px;">Paso crítico</span>
                @endif
              </span>
              <span class="step-pct {{ $txtClass }}">{{ $pct }}% éxito · {{ $step['tasa_fallo'] }}% fallo</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill {{ $pgClass }}" style="width:{{ min(100, max(0, $pct)) }}%"></div>
            </div>
          </div>
        @endforeach

        <div style="margin-top:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;">
          <table class="data" style="margin:0;">
            <thead>
              <tr>
                <th>Paso</th>
                <th>Nombre</th>
                <th class="td-right">% Éxito</th>
                <th class="td-right">% Fallo</th>
                <th class="td-right">Evaluaciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($steps as $step)
                <tr>
                  <td class="td-center">{{ $step['numero'] }}</td>
                  <td>{{ $step['nombre'] }}</td>
                  <td class="td-right {{ $step['promedio'] >= 75 ? 'text-green' : ($step['promedio'] >= 50 ? 'text-yellow' : 'text-red') }} fw-bold">{{ $step['promedio'] }}%</td>
                  <td class="td-right {{ $step['tasa_fallo'] >= 50 ? 'text-red' : 'text-yellow' }}">{{ $step['tasa_fallo'] }}%</td>
                  <td class="td-right">{{ $step['total'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        @php
          $stepsCol  = collect($steps);
          $mejorPaso = $stepsCol->sortByDesc('promedio')->first();
          $peorPaso  = $stepsCol->sortBy('promedio')->first();
        @endphp
        @if($mejorPaso && $stepsCol->count() > 1)
        <div style="margin-top:12px;border:1px solid #cbd5e1;border-left:3px solid #64748b;border-radius:4px;padding:10px 14px;background:#f8fafc;">
          <p style="margin:0 0 4px;font-size:9.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.4px;">Resumen del análisis</p>
          <p style="margin:0 0 5px;font-size:9px;color:#334155;">
            <strong>Paso mejor dominado:</strong>
            Paso {{ $mejorPaso['numero'] }} &mdash; {{ $mejorPaso['nombre'] }}.
            El <strong>{{ $mejorPaso['promedio'] }}%</strong> de los aprendices lo realiza correctamente;
            solo el <strong>{{ $mejorPaso['tasa_fallo'] }}%</strong> falla en este paso.
          </p>
          <p style="margin:0;font-size:9px;color:#334155;">
            <strong>Paso que más les cuesta:</strong>
            Paso {{ $peorPaso['numero'] }} &mdash; {{ $peorPaso['nombre'] }}.
            Solo el <strong>{{ $peorPaso['promedio'] }}%</strong> lo realiza correctamente;
            el <strong>{{ $peorPaso['tasa_fallo'] }}%</strong> de los aprendices falla aquí.
            Se recomienda reforzar el entrenamiento en este paso.
          </p>
        </div>
        @endif
      @endif
      </div>{{-- /section-body --}}
    </div>
  @endif

</body>
</html>
