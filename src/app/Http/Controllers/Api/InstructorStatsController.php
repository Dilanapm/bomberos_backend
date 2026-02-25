<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\StatsHelper;
use App\Http\Controllers\Controller;
use App\Models\EppEvaluation;
use App\Models\EppStepEvaluation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorStatsController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/stats/my-group
    //  Estadísticas globales del grupo del instructor.
    //
    //  "Grupo" = todos los aprendices con al menos una evaluación,
    //  ya que no existe una relación instructor→aprendiz explícita.
    // ──────────────────────────────────────────────────────────────
    public function myGroup(Request $request): JsonResponse
    {
        // ── Aprendices con evaluaciones ──────────────────────────────
        $aprendices = User::role('aprendiz')
            ->whereHas('evaluations')
            ->with([
                'evaluations' => fn($q) => $q
                    ->selectRaw('user_id, AVG(general_score) as avg_score, COUNT(*) as total')
                    ->groupBy('user_id'),
            ])
            ->get();

        $totalAprendices = $aprendices->count();

        // Promedio de cada aprendiz
        $promedios = $aprendices
            ->map(fn($u) => (float) optional($u->evaluations->first())->avg_score)
            ->filter(fn($v) => $v > 0)
            ->values()
            ->all();

        $promedioGrupo = round(StatsHelper::mean($promedios), 2);
        $stdGrupo      = round(StatsHelper::standardDeviation($promedios), 2);
        $consistency   = StatsHelper::consistencyLevel($promedios);

        // Tasa de aprobación (evaluaciones con status = aprobado)
        $totalEvals    = EppEvaluation::count();
        $totalAprobado = EppEvaluation::where('status', 'aprobado')->count();
        $tasaAprobacion = $totalEvals > 0 ? round(($totalAprobado / $totalEvals) * 100, 1) : 0;

        // Mejor aprendiz
        $mejorAprendiz = $aprendices
            ->sortByDesc(fn($u) => optional($u->evaluations->first())->avg_score)
            ->first();

        $mejorPromedio = $mejorAprendiz
            ? round((float) optional($mejorAprendiz->evaluations->first())->avg_score, 2)
            : null;

        // Aprendices que necesitan apoyo (promedio < 60)
        $necesitanApoyo = $aprendices
            ->filter(fn($u) => (float) optional($u->evaluations->first())->avg_score < 60)
            ->count();

        // Comparativa vs institución (promedio de TODOS los aprendices = mismo conjunto)
        // Como no hay multi-institución, se usa el promedio global comme referencia
        $promedioInstitucional = $promedioGrupo; // mismo dato cuando solo hay 1 grupo

        // Promedio del instructor logueado (sus evaluaciones propias si las tiene)
        $myAvg = EppEvaluation::where('user_id', $request->user()->id)->avg('general_score');

        return response()->json([
            'success' => true,
            'data'    => [
                'resumen_grupo' => [
                    'total_aprendices'  => $totalAprendices,
                    'promedio_grupo'    => $promedioGrupo,
                    'tasa_aprobacion'   => $tasaAprobacion,
                    'mejor_aprendiz'    => $mejorAprendiz ? [
                        'name'    => $mejorAprendiz->name,
                        'promedio' => $mejorPromedio,
                    ] : null,
                    'necesitan_apoyo'   => $necesitanApoyo,
                ],

                'vs_institucion' => [
                    'mi_grupo_promedio'        => $promedioGrupo,
                    'promedio_institucional'   => $promedioInstitucional,
                    'diferencia'               => round($promedioGrupo - $promedioInstitucional, 2),
                    'interpretacion'           => 'Estadísticas del sistema completo (grupo único).',
                ],

                'consistencia_grupo' => [
                    'desviacion_estandar' => $stdGrupo,
                    'nivel'               => $consistency,
                    'interpretacion'      => StatsHelper::consistencyInterpretation(
                        $consistency,
                        'los resultados del grupo'
                    ),
                ],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/stats/ranking
    //  Ranking de todos los aprendices ordenado por promedio desc.
    // ──────────────────────────────────────────────────────────────
    public function ranking(): JsonResponse
    {
        $aprendices = User::role('aprendiz')
            ->whereHas('evaluations')
            ->withCount('evaluations')
            ->with([
                'evaluations' => fn($q) => $q
                    ->selectRaw('user_id, AVG(general_score) as avg_score, MAX(general_score) as best_score, COUNT(*) as total')
                    ->groupBy('user_id'),
            ])
            ->get()
            ->map(function (User $user) {
                $eval    = $user->evaluations->first();
                $average = round((float) optional($eval)->avg_score, 2);

                // Calcular tendencia a partir de las últimas 5 evaluaciones
                $scores = EppEvaluation::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->pluck('general_score')
                    ->map(fn($s) => (float) $s)
                    ->reverse()
                    ->values()
                    ->all();

                return [
                    'aprendiz_id'  => $user->id,
                    'name'         => $user->name,
                    'promedio'     => $average,
                    'intentos'     => $user->evaluations_count,
                    'mejor_puntaje' => round((float) optional($eval)->best_score, 2),
                    'tendencia'    => StatsHelper::detectTrend($scores),
                    'badge'        => StatsHelper::badge($average),
                ];
            })
            ->sortByDesc('promedio')
            ->values()
            ->map(function ($item, $index) {
                return array_merge(['posicion' => $index + 1], $item);
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'ranking' => $aprendices,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/stats/need-help
    //  Aprendices que requieren atención especial.
    //
    //  Criterios:
    //    - Promedio general < 65  → prioridad alta
    //    - Algún paso con promedio < 50 % (score*100 < 50)
    // ──────────────────────────────────────────────────────────────
    public function needHelp(): JsonResponse
    {
        $aprendices = User::role('aprendiz')
            ->whereHas('evaluations')
            ->with([
                'evaluations' => fn($q) => $q
                    ->selectRaw('user_id, AVG(general_score) as avg_score')
                    ->groupBy('user_id'),
            ])
            ->get()
            ->filter(function (User $user) {
                $avg = (float) optional($user->evaluations->first())->avg_score;
                return $avg < 65 && $avg > 0;
            });

        $result = $aprendices->map(function (User $user) {
            $promedioGeneral = round((float) optional($user->evaluations->first())->avg_score, 2);

            // Paso más débil del aprendiz
            $pasoDebil = EppStepEvaluation::whereHas(
                'evaluation',
                fn($q) => $q->where('user_id', $user->id)
            )
                ->selectRaw('step_number, step_name, AVG(score) * 100 as avg_score_pct, COUNT(*) as attempts')
                ->groupBy('step_number', 'step_name')
                ->orderBy('avg_score_pct', 'asc')
                ->first();

            $prioridad     = $promedioGeneral < 50 ? 'alta' : 'media';
            $problema      = $pasoDebil
                ? "Falla consistentemente Paso {$pasoDebil->step_number} ({$pasoDebil->step_name})"
                : 'Bajo rendimiento general';

            $recomendacion = $pasoDebil
                ? "Sesión 1-a-1 para Paso {$pasoDebil->step_number} ({$pasoDebil->step_name})"
                : 'Revisar metodología general con el aprendiz';

            return [
                'id'               => $user->id,
                'name'             => $user->name,
                'problema'         => $problema,
                'promedio_paso'    => $pasoDebil ? round((float) $pasoDebil->avg_score_pct, 1) : null,
                'promedio_general' => $promedioGeneral,
                'prioridad'        => $prioridad,
                'recomendacion'    => $recomendacion,
            ];
        })->sortByDesc(fn($item) => $item['prioridad'] === 'alta' ? 1 : 0)->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'total'      => $result->count(),
                'aprendices' => $result,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/stats/step-analysis
    //  Rendimiento promedio por cada paso del EPP en todo el grupo.
    // ──────────────────────────────────────────────────────────────
    public function stepAnalysis(): JsonResponse
    {
        $pasos = EppStepEvaluation::selectRaw(
            'step_number,
             step_name,
             AVG(score) * 100 as avg_score_pct,
             COUNT(*) as total_evaluations'
        )
            ->groupBy('step_number', 'step_name')
            ->orderBy('step_number', 'asc')
            ->get();

        $minAvg  = $pasos->min('avg_score_pct');

        $mapped = $pasos->map(function ($paso) use ($minAvg) {
            $avg        = round((float) $paso->avg_score_pct, 1);
            $difficulty = StatsHelper::difficultyLevel($avg);
            $esMasDificil = (round((float) $paso->avg_score_pct, 1) == round((float) $minAvg, 1));

            return [
                'numero'          => $paso->step_number,
                'nombre'          => $paso->step_name,
                'promedio_grupo'  => $avg,
                'tasa_exito'      => $avg,   // Porcentaje de acierto = promedio porcentual
                'dificultad'      => $difficulty,
                'problema'        => $esMasDificil ? 'Paso más difícil del grupo' : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'pasos' => $mapped,
            ],
        ]);
    }
}
