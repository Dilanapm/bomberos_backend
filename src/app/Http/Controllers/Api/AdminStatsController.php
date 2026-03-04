<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\StatsHelper;
use App\Http\Controllers\Controller;
use App\Models\EppEvaluation;
use App\Models\EppStepEvaluation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/admin/stats/global
    //  Vista general del sistema completo.
    // ──────────────────────────────────────────────────────────────
    public function global(): JsonResponse
    {
        $totalUsuarios    = User::count();
        $totalEvals       = EppEvaluation::count();
        $totalAprobadas   = EppEvaluation::where('status', 'aprobado')->count();
        $tasaAprobacion   = $totalEvals > 0
            ? round(($totalAprobadas / $totalEvals) * 100, 1)
            : 0;
        $tiempoPromedio   = round((float) EppEvaluation::avg('duration_seconds'), 1);

        // Estadísticas descriptivas de los puntajes generales
        $scores = EppEvaluation::pluck('general_score')
            ->map(fn($s) => (float) $s)
            ->all();

        $promedioGlobal  = round(StatsHelper::mean($scores), 2);
        $rangoTipico     = StatsHelper::typicalRange($scores);
        $puntajeMasComun = StatsHelper::mode($scores);
        $consistency     = StatsHelper::consistencyLevel($scores);

        // Uso del sistema
        $hoy    = now()->toDateString();
        $semana = now()->subDays(7)->toDateString();
        $mes    = now()->subDays(30)->toDateString();

        $evalsHoy    = EppEvaluation::whereDate('created_at', $hoy)->count();
        $evalsSemana = EppEvaluation::whereDate('created_at', '>=', $semana)->count();
        $evalsMes    = EppEvaluation::whereDate('created_at', '>=', $mes)->count();

        // Pico de uso por hora (hora UTC con más evaluaciones)
        $picoHora = EppEvaluation::selectRaw('EXTRACT(HOUR FROM created_at)::int as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderByDesc('total')
            ->first();

        $picoUso = $picoHora
            ? str_pad((string) (int) $picoHora->hora, 2, '0', STR_PAD_LEFT) . ':00-'
            . str_pad((string) (((int) $picoHora->hora + 2) % 24), 2, '0', STR_PAD_LEFT) . ':00'
            : 'No disponible';

        return response()->json([
            'success' => true,
            'data'    => [
                'metricas_globales' => [
                    'total_usuarios'          => $totalUsuarios,
                    'total_evaluaciones'       => $totalEvals,
                    'tasa_aprobacion_general' => $tasaAprobacion,
                    'tiempo_promedio_ejercicio' => $tiempoPromedio,
                ],

                'rendimiento_general' => [
                    'promedio_global'   => $promedioGlobal,
                    'rango_tipico'      => $rangoTipico,
                    'resultado_mas_comun' => $puntajeMasComun,
                    'nivel_consistencia' => $consistency,
                    'interpretacion'     => StatsHelper::consistencyInterpretation(
                        $consistency,
                        'los puntajes del sistema'
                    ),
                ],

                'uso_sistema' => [
                    'evaluaciones_hoy'    => $evalsHoy,
                    'evaluaciones_semana' => $evalsSemana,
                    'evaluaciones_mes'    => $evalsMes,
                    'pico_uso'            => $picoUso,
                ],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/admin/stats/instructors
    //  Comparativa entre instructores.
    // ──────────────────────────────────────────────────────────────
    public function instructors(): JsonResponse
    {
        // Cada instructor con los promedios de sus aprendices
        $instructores = User::role('instructor')
            ->get()
            ->map(function (User $instructor) {
                // Aprendices = todos los aprendices con evaluaciones (sin relación directa)
                // Para tener datos por instructor, obtenemos los aprendices que
                // comentaron evaluaciones de este instructor o agrupar por cualquier heurística.
                // Como no hay tabla instructor_aprendiz, usamos todos los aprendices del sistema
                // y mostramos estadísticas globales por instructor (comentarios hechos).
                // Para una métrica significativa: usamos los puntajes de evaluaciones que
                // el instructor ha comentado (evaluaciones "revisadas").
                $aprendicesIds = \App\Models\EppInstructorComment::where('instructor_id', $instructor->id)
                    ->distinct()
                    ->pluck('evaluation_id');

                $evalsPropias = EppEvaluation::whereIn('id', $aprendicesIds)->get();

                if ($evalsPropias->isEmpty()) {
                    return null; // Instructor sin actividad
                }

                $scores = $evalsPropias->pluck('general_score')->map(fn($s) => (float) $s)->all();

                $promedio       = round(StatsHelper::mean($scores), 2);
                $aprobadas      = $evalsPropias->where('status', 'aprobado')->count();
                $tasaAprobacion = count($scores) > 0
                    ? round(($aprobadas / count($scores)) * 100, 1)
                    : 0;
                $consistency    = StatsHelper::consistencyLevel($scores);

                return [
                    'id'               => $instructor->id,
                    'name'             => $instructor->name,
                    'aprendices'       => $evalsPropias->groupBy('user_id')->count(),
                    'promedio_grupo'   => $promedio,
                    'tasa_aprobacion'  => $tasaAprobacion,
                    'consistencia'     => $consistency,
                    'alerta'           => $consistency === 'baja'
                        ? 'Grupo con alta variabilidad en puntajes'
                        : null,
                ];
            })
            ->filter()
            ->sortByDesc('promedio_grupo')
            ->values()
            ->map(function ($item, $index) {
                return array_merge(['ranking' => $index + 1], $item);
            });

        if ($instructores->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'instructores'             => [],
                    'diferencia_max_min'        => 0,
                    'instructor_mas_consistente' => null,
                ],
            ]);
        }

        $promedios       = $instructores->pluck('promedio_grupo')->all();
        $diferenciaMaxMin = count($promedios) > 1
            ? round(max($promedios) - min($promedios), 2)
            : 0;

        $masConsistente = $instructores
            ->where('consistencia', 'alta')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'instructores'             => $instructores->values(),
                'diferencia_max_min'        => $diferenciaMaxMin,
                'instructor_mas_consistente' => $masConsistente
                    ? $masConsistente['name']
                    : ($instructores->first()['name'] ?? null),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/admin/stats/step-analysis
    //  Análisis de pasos problemáticos a nivel institucional.
    // ──────────────────────────────────────────────────────────────
    public function stepAnalysis(): JsonResponse
    {
        $pasos = EppStepEvaluation::selectRaw(
            'step_number,
             step_name,
             AVG(score) * 100     as avg_score_pct,
             (1 - AVG(score)) * 100 as tasa_fallo,
             STDDEV(score) * 100  as std_score,
             COUNT(*)             as total'
        )
            ->groupBy('step_number', 'step_name')
            ->orderBy('step_number', 'asc')
            ->get();

        $minAvg = $pasos->min('avg_score_pct');

        $mapped = $pasos->map(function ($paso) use ($minAvg) {
            $avg       = round((float) $paso->avg_score_pct, 1);
            $fallo     = round((float) $paso->tasa_fallo, 1);
            $std       = round((float) $paso->std_score, 1);
            $variacion = StatsHelper::consistencyLevel(
                array_fill(0, 10, $avg) // Aproximación de variación con std
            );

            // Si la desviación es alta, marcar como variación alta
            if ($std > 20) {
                $variacion = 'alta';
            } elseif ($std > 10) {
                $variacion = 'media';
            } else {
                $variacion = 'baja';
            }

            $esMasDificil = round($avg, 1) == round((float) $minAvg, 1);

            return [
                'numero'                 => $paso->step_number,
                'nombre'                 => $paso->step_name,
                'promedio_institucional' => $avg,
                'tasa_fallo'             => $fallo,
                'variacion'              => $variacion,
                'es_mas_dificil'         => $esMasDificil,
                'recomendacion'          => $esMasDificil
                    ? 'Revisar material de entrenamiento para este paso'
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'pasos' => $mapped,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/admin/stats/trends
    //  Tendencias temporales mensuales del sistema.
    // ──────────────────────────────────────────────────────────────
    public function trends(): JsonResponse
    {
        // Últimos 6 meses
        $meses = EppEvaluation::selectRaw(
            "TO_CHAR(created_at, 'YYYY-MM') as periodo,
             TO_CHAR(created_at, 'FMMonth YYYY') as nombre_mes,
             AVG(general_score)               as promedio,
             SUM(CASE WHEN status = 'aprobado' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as aprobacion,
             COUNT(*) as total"
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('periodo', 'nombre_mes')
            ->orderBy('periodo', 'asc')
            ->get();

        $evolucion = $meses->map(fn($m) => [
            'mes'      => $m->nombre_mes,
            'promedio' => round((float) $m->promedio, 1),
            'aprobacion' => round((float) $m->aprobacion, 1),
            'total'    => (int) $m->total,
        ])->values();

        // Tendencia general
        $promediosSerie = $evolucion->pluck('promedio')->all();
        $tendencia      = StatsHelper::detectTrend($promediosSerie);
        $mejoraMensual  = StatsHelper::trendSlope($promediosSerie);

        // Proyección próximo mes (último promedio + pendiente)
        $ultimoPromedio   = !empty($promediosSerie) ? end($promediosSerie) : 0;
        $proyeccion       = round($ultimoPromedio + $mejoraMensual, 1);

        return response()->json([
            'success' => true,
            'data'    => [
                'evolucion_mensual'      => $evolucion,
                'tendencia'              => $tendencia,
                'mejora_mensual'         => $mejoraMensual,
                'proyeccion_proximo_mes' => $proyeccion,
                'interpretacion'         => match ($tendencia) {
                    'positiva' => "Los puntajes muestran una mejora continua de {$mejoraMensual} puntos por mes.",
                    'negativa' => "Los puntajes han bajado un promedio de " . abs($mejoraMensual) . " puntos por mes. Se recomienda revisar el programa.",
                    default    => 'Los puntajes se mantienen estables en el período analizado.',
                },
            ],
        ]);
    }
}
