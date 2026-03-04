<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Helpers\StatsHelper;
use App\Http\Controllers\Controller;
use App\Models\EppEvaluation;
use App\Models\EppInstructorComment;
use App\Models\EppStepEvaluation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    /**
     * Genera el reporte PDF de estadísticas con las secciones elegidas.
     * GET /admin/reports/statistics?sections[]=global&sections[]=instructors...
     */
    public function statistics(Request $request): Response
    {
        $sections = $request->input('sections', [
            'global', 'chart', 'instructors', 'steps',
        ]);

        $data = [];

        // ── 1. Métricas globales ──────────────────────────────────
        if (in_array('global', $sections)) {
            $totalEvals    = EppEvaluation::count();
            $aprobadas     = EppEvaluation::where('status', 'aprobado')->count();
            $reprobadas    = EppEvaluation::where('status', 'reprobado')->count();
            $incompletas   = EppEvaluation::where('status', 'incompleto')->count();
            $tasaAprobacion = $totalEvals > 0
                ? round(($aprobadas / $totalEvals) * 100, 1)
                : 0.0;

            $data['global'] = [
                'total_usuarios'     => User::count(),
                'instructores'       => User::role('instructor')->count(),
                'aprendices'         => User::role('aprendiz')->count(),
                'total_evals'        => $totalEvals,
                'aprobadas'          => $aprobadas,
                'reprobadas'         => $reprobadas,
                'incompletas'        => $incompletas,
                'tasa_aprobacion'    => $tasaAprobacion,
                'tiempo_promedio'    => round((float) EppEvaluation::avg('duration_seconds'), 1),
                'evals_hoy'          => EppEvaluation::whereDate('created_at', now()->toDateString())->count(),
                'evals_semana'       => EppEvaluation::whereDate('created_at', '>=', now()->subDays(7)->toDateString())->count(),
                'evals_mes'          => EppEvaluation::whereDate('created_at', '>=', now()->subDays(30)->toDateString())->count(),
            ];
        }

        // ── 2. Datos gráfica / tabla 30 días ─────────────────────
        if (in_array('chart', $sections)) {
            $rows = EppEvaluation::selectRaw("DATE(created_at) as dia, COUNT(*) as total")
                ->where('created_at', '>=', now()->subDays(29)->startOfDay())
                ->groupBy('dia')
                ->orderBy('dia')
                ->pluck('total', 'dia');

            $chartData = [];
            for ($i = 29; $i >= 0; $i--) {
                $date        = now()->subDays($i)->format('Y-m-d');
                $chartData[] = [
                    'label' => now()->subDays($i)->format('d/m'),
                    'value' => (int) ($rows[$date] ?? 0),
                ];
            }

            // ── Fix: max() sobre el array de valores, no dos argumentos ──
            $values  = array_column($chartData, 'value');
            $realMax = !empty($values) ? (int) max($values) : 0;
            $chartMax = $realMax > 0 ? $realMax : 1; // sin división por cero

            $total   = array_sum($values);
            $activos = count(array_filter($values, fn($v) => $v > 0));

            // Día más activo
            $maxDayLabel = '—';
            if ($realMax > 0) {
                $maxIdx = array_search($realMax, $values);
                $maxDayLabel = $maxIdx !== false ? $chartData[$maxIdx]['label'] : '—';
            }

            // Desglose semanal (5 grupos de 6 días)
            $weeks = [];
            for ($w = 0; $w < 5; $w++) {
                $slice = array_slice($chartData, $w * 6, 6);
                if (empty($slice)) break;
                $sv = array_column($slice, 'value');
                $weeks[] = [
                    'desde' => $slice[0]['label'],
                    'hasta' => $slice[count($slice) - 1]['label'],
                    'total' => array_sum($sv),
                    'max'   => !empty($sv) ? (int) max($sv) : 0,
                    'prom'  => round(array_sum($sv) / count($sv), 1),
                ];
            }

            $data['chart']        = $chartData;
            $data['chartMax']     = $chartMax;
            $data['chartSummary'] = [
                'total'          => $total,
                'promedio_diario' => round($total / 30, 1),
                'max_val'        => $realMax,
                'max_dia_label'  => $maxDayLabel,
                'dias_activos'   => $activos,
                'dias_sin'       => 30 - $activos,
            ];
            $data['chartWeeks'] = $weeks;
        }

        // ── 3. Comparativa de instructores ────────────────────────
        if (in_array('instructors', $sections)) {
            $instructores = User::role('instructor')
                ->get()
                ->map(function (User $instructor) {
                    $evalIds = EppInstructorComment::where('instructor_id', $instructor->id)
                        ->distinct()->pluck('evaluation_id');
                    $evals   = EppEvaluation::whereIn('id', $evalIds)->get();
                    if ($evals->isEmpty()) return null;

                    $scores         = $evals->pluck('general_score')->map(fn($s) => (float) $s)->all();
                    $aprobadas      = $evals->where('status', 'aprobado')->count();
                    $tasaAprobacion = count($scores) > 0
                        ? round(($aprobadas / count($scores)) * 100, 1)
                        : 0.0;

                    return [
                        'name'            => $instructor->name,
                        'aprendices'      => $evals->groupBy('user_id')->count(),
                        'promedio_grupo'  => round(StatsHelper::mean($scores), 2),
                        'tasa_aprobacion' => $tasaAprobacion,
                        'consistencia'    => StatsHelper::consistencyLevel($scores),
                    ];
                })
                ->filter()
                ->sortByDesc('promedio_grupo')
                ->values()
                ->map(fn($item, $idx) => array_merge(['ranking' => $idx + 1], $item))
                ->all();

            $data['instructors'] = $instructores;
        }

        // ── 4. Análisis de pasos EPP ──────────────────────────────
        if (in_array('steps', $sections)) {
            $pasos = EppStepEvaluation::selectRaw(
                'step_number, step_name,
                 AVG(score) * 100       as avg_score_pct,
                 (1 - AVG(score)) * 100 as tasa_fallo,
                 COUNT(*)               as total'
            )
                ->groupBy('step_number', 'step_name')
                ->orderBy('step_number')
                ->get();

            $minAvg = $pasos->min('avg_score_pct');

            $data['steps'] = $pasos->map(fn($paso) => [
                'numero'     => $paso->step_number,
                'nombre'     => $paso->step_name,
                'promedio'   => round((float) $paso->avg_score_pct, 1),
                'tasa_fallo' => round((float) $paso->tasa_fallo, 1),
                'es_critico' => round((float) $paso->avg_score_pct, 1) == round((float) $minAvg, 1),
                'total'      => (int) $paso->total,
            ])->all();
        }

        $data['sections']      = $sections;
        $data['generated_at']  = now()->format('d/m/Y H:i');
        $data['generated_by']  = auth()->user()->name;

        // Logo como base64 para que dompdf lo embeba sin problemas de ruta
        $logoPath = storage_path('app/public/logos/logo_sin_fondo.png');
        $data['logo_b64'] = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('reports.statistics-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true);

        $filename = 'reporte-estadisticas-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
