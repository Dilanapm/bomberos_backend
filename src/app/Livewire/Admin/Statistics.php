<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Helpers\StatsHelper;
use App\Models\EppEvaluation;
use App\Models\EppStepEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Statistics extends Component
{
    // ── Global metric cards ───────────────────────────────────────
    public int    $totalUsuarios    = 0;
    public int    $totalEvals       = 0;
    public float  $tasaAprobacion   = 0.0;
    public float  $tiempoPromedio   = 0.0;

    // ── Usage summary ─────────────────────────────────────────────
    public int    $evalsHoy         = 0;
    public int    $evalsSemana      = 0;
    public int    $evalsMes         = 0;
    public string $picoUso          = 'N/A';

    // ── 30-day chart data ─────────────────────────────────────────
    public array  $chartLabels      = [];
    public array  $chartValues      = [];

    // ── Instructor comparison table ───────────────────────────────
    public array  $instructores     = [];

    // ── Problematic steps ─────────────────────────────────────────
    public array  $steps            = [];

    // ── Last refreshed timestamp ──────────────────────────────────
    public string $lastUpdated      = '';

    // ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
        $this->dispatch('stats-refreshed');
    }

    // ─────────────────────────────────────────────────────────────

    private function loadStats(): void
    {
        $this->loadGlobalMetrics();
        $this->loadChartData();
        $this->loadInstructors();
        $this->loadStepAnalysis();
        $this->lastUpdated = now()->format('d/m/Y H:i:s');
    }

    // ── 1. Global metrics ─────────────────────────────────────────
    private function loadGlobalMetrics(): void
    {
        $this->totalUsuarios  = User::count();
        $this->totalEvals     = EppEvaluation::count();

        $aprobadas = EppEvaluation::where('status', 'aprobado')->count();
        $this->tasaAprobacion = $this->totalEvals > 0
            ? round(($aprobadas / $this->totalEvals) * 100, 1)
            : 0.0;

        $this->tiempoPromedio = round(
            (float) EppEvaluation::avg('duration_seconds'),
            1
        );

        $hoy   = now()->toDateString();
        $sem   = now()->subDays(7)->toDateString();
        $mes   = now()->subDays(30)->toDateString();

        $this->evalsHoy     = EppEvaluation::whereDate('created_at', $hoy)->count();
        $this->evalsSemana  = EppEvaluation::whereDate('created_at', '>=', $sem)->count();
        $this->evalsMes     = EppEvaluation::whereDate('created_at', '>=', $mes)->count();

        $picoHora = EppEvaluation::selectRaw('EXTRACT(HOUR FROM created_at)::int as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderByDesc('total')
            ->first();

        $this->picoUso = $picoHora
            ? str_pad((string) (int) $picoHora->hora, 2, '0', STR_PAD_LEFT) . ':00‑'
              . str_pad((string) (((int) $picoHora->hora + 2) % 24), 2, '0', STR_PAD_LEFT) . ':00'
            : 'Sin datos';
    }

    // ── 2. 30-day chart (evaluations per day) ─────────────────────
    private function loadChartData(): void
    {
        $rows = EppEvaluation::selectRaw(
            'DATE(created_at) as dia, COUNT(*) as total'
        )
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        // Build a full 30-day range filling zeros for missing days
        $labels = [];
        $values = [];
        for ($i = 29; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $label    = now()->subDays($i)->format('d/m');
            $labels[] = $label;
            $values[] = (int) ($rows[$date] ?? 0);
        }

        $this->chartLabels = $labels;
        $this->chartValues = $values;
    }

    // ── 3. Instructor comparison ──────────────────────────────────
    private function loadInstructors(): void
    {
        $instructores = User::role('instructor')
            ->get()
            ->map(function (User $instructor) {
                $evalIds = \App\Models\EppInstructorComment::where('instructor_id', $instructor->id)
                    ->distinct()
                    ->pluck('evaluation_id');

                $evals = EppEvaluation::whereIn('id', $evalIds)->get();

                if ($evals->isEmpty()) {
                    return null;
                }

                $scores         = $evals->pluck('general_score')->map(fn($s) => (float) $s)->all();
                $aprobadas      = $evals->where('status', 'aprobado')->count();
                $tasaAprobacion = count($scores) > 0
                    ? round(($aprobadas / count($scores)) * 100, 1)
                    : 0.0;
                $consistencia   = StatsHelper::consistencyLevel($scores);

                return [
                    'id'              => $instructor->id,
                    'name'            => $instructor->name,
                    'aprendices'      => $evals->groupBy('user_id')->count(),
                    'promedio_grupo'  => round(StatsHelper::mean($scores), 2),
                    'tasa_aprobacion' => $tasaAprobacion,
                    'consistencia'    => $consistencia,
                    'alerta'          => $consistencia === 'baja'
                        ? 'Alta variabilidad en puntajes'
                        : null,
                ];
            })
            ->filter()
            ->sortByDesc('promedio_grupo')
            ->values()
            ->map(fn($item, $idx) => array_merge(['ranking' => $idx + 1], $item))
            ->all();

        $this->instructores = $instructores;
    }

    // ── 4. Step analysis ─────────────────────────────────────────
    private function loadStepAnalysis(): void
    {
        $pasos = EppStepEvaluation::selectRaw(
            'step_number,
             step_name,
             AVG(score) * 100       as avg_score_pct,
             (1 - AVG(score)) * 100 as tasa_fallo,
             STDDEV(score) * 100    as std_score,
             COUNT(*)               as total'
        )
            ->groupBy('step_number', 'step_name')
            ->orderBy('step_number')
            ->get();

        $minAvg = $pasos->min('avg_score_pct');

        $this->steps = $pasos->map(function ($paso) use ($minAvg) {
            $avg   = round((float) $paso->avg_score_pct, 1);
            $fallo = round((float) $paso->tasa_fallo, 1);
            $std   = round((float) $paso->std_score, 1);

            if ($std > 20) {
                $variacion = 'alta';
            } elseif ($std > 10) {
                $variacion = 'media';
            } else {
                $variacion = 'baja';
            }

            return [
                'numero'      => $paso->step_number,
                'nombre'      => $paso->step_name,
                'promedio'    => $avg,
                'tasa_fallo'  => $fallo,
                'variacion'   => $variacion,
                'es_critico'  => round($avg, 1) == round((float) $minAvg, 1),
            ];
        })->all();
    }

    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.statistics')
            ->layout('components.layouts.admin', [
                'title'    => 'Estadísticas',
                'subtitle' => 'Métricas globales del sistema EPP',
            ]);
    }
}
