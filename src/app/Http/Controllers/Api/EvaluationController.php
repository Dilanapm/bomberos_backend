<?php

namespace App\Http\Controllers\Api;

use App\Helpers\StatsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEvaluationRequest;
use App\Models\EppEvaluation;
use App\Models\EppStepEvaluation;
use App\Models\EppError;
use App\Models\EppTimeline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/evaluations
    //  Guarda el resultado completo enviado por FastAPI.
    // ──────────────────────────────────────────────────────────────
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $authUser = $request->user();

        // Instructor puede registrar en nombre de un aprendiz pasando user_id.
        // Si no lo especifica, se guarda sobre su propio ID.
        if ($authUser->hasRole('instructor') && !empty($data['user_id'])) {
            $userId = $data['user_id'];
        } else {
            $userId = $authUser->id;
        }

        // Solo los aprendices pueden ser sujeto de una evaluación EPP.
        $targetUser = User::findOrFail($userId);
        if (! $targetUser->hasRole('aprendiz')) {
            return response()->json([
                'success' => false,
                'message' => 'Las evaluaciones EPP solo pueden registrarse para usuarios con rol aprendiz.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Evaluación principal
            $evaluation = EppEvaluation::create([
                'session_id'            => $data['session_id'],
                'user_id'               => $userId,
                'general_score'         => $data['general_score'],
                'total_steps'           => $data['total_steps'],
                'steps_completed'       => $data['steps_completed'],
                'correct_order'         => $data['correct_order'],
                'duration_seconds'      => $data['duration_seconds'],
                'total_frames'          => $data['total_frames'],
                'frames_with_detection' => $data['frames_with_detection'],
                'detection_rate'        => $data['detection_rate'],
                'status'                => $this->resolveStatus($data),
                'recommendations'       => $data['recommendations'] ?? null,
            ]);

            // 2. Los 6 pasos
            $steps = array_map(fn($step) => array_merge($step, [
                'evaluation_id' => $evaluation->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]), $data['steps_evaluation']);

            EppStepEvaluation::insert($steps);

            // 3. Errores (opcional)
            if (!empty($data['errors'])) {
                $errors = array_map(fn($error) => array_merge($error, [
                    'evaluation_id' => $evaluation->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]), $data['errors']);

                EppError::insert($errors);
            }

            // 4. Timeline (opcional)
            if (!empty($data['timeline'])) {
                // Insertar en chunks para no sobrecargar en videos largos
                foreach (array_chunk($data['timeline'], 500) as $chunk) {
                    $rows = array_map(fn($point) => [
                        'evaluation_id' => $evaluation->id,
                        'time'          => $point['time'],
                        'step_detected' => $point['step_detected'],
                        'confidence'    => $point['confidence'],
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ], $chunk);

                    EppTimeline::insert($rows);
                }
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Evaluación guardada correctamente.',
                'evaluation_id' => $evaluation->id,
                'status'        => $evaluation->status,
                'general_score' => $evaluation->general_score,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la evaluación.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/evaluations
    //  Historial paginado del aprendiz autenticado.
    // ──────────────────────────────────────────────────────────────
    public function history(Request $request): JsonResponse
    {
        $evaluations = EppEvaluation::forUser($request->user()->id)
            ->with(['steps', 'errors'])
            ->recent()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $evaluations,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/evaluations/{id}            (aprendiz: solo la suya)
    //  GET /api/v1/instructor/evaluations/{id} (instructor: cualquiera)
    //  Detalle completo de una evaluación.
    // ──────────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $query = EppEvaluation::with(['steps', 'errors', 'timeline', 'comments.instructor'])
            ->where('id', $id);

        // El aprendiz solo puede ver sus propias evaluaciones.
        if ($request->user()->hasRole('aprendiz')) {
            $query->where('user_id', $request->user()->id);
        }

        $evaluation = $query->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => $evaluation,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/evaluations/stats
    //  Estadísticas del aprendiz autenticado (mejoradas).
    // ──────────────────────────────────────────────────────────────
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $base = EppEvaluation::forUser($userId);

        $totalAttempts = (clone $base)->count();
        $approved      = (clone $base)->approved()->count();
        $averageScore  = (clone $base)->avg('general_score');
        $bestScore     = (clone $base)->max('general_score');
        $lastEval      = (clone $base)->recent()->with('steps')->first();

        // Puntajes del aprendiz para cálculos estadísticos
        $myScores = (clone $base)
            ->pluck('general_score')
            ->map(fn($s) => (float) $s)
            ->all();

        // Desviación estándar / rango típico / moda / consistencia
        $rangoTipico      = StatsHelper::typicalRangeDelta($myScores);
        $resultadoMasComun = StatsHelper::mode($myScores);
        $consistency      = StatsHelper::consistencyLevel($myScores);

        // Promedio global del grupo (todos los aprendices) para comparar
        $promedioGrupo = (float) EppEvaluation::avg('general_score');

        // Paso más difícil (menor promedio de score)
        $hardestStep = EppStepEvaluation::whereHas(
            'evaluation',
            fn($q) => $q->where('user_id', $userId)
        )
            ->selectRaw('step_number, step_name, AVG(score) * 100 as avg_score, COUNT(*) as attempts')
            ->groupBy('step_number', 'step_name')
            ->orderBy('avg_score', 'asc')
            ->first();

        // Progreso en el tiempo (últimas 10 evaluaciones)
        $progress = (clone $base)
            ->recent()
            ->limit(10)
            ->get(['id', 'general_score', 'status', 'steps_completed', 'created_at'])
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_attempts'    => $totalAttempts,
                'approved'          => $approved,
                'failed'            => $totalAttempts - $approved,
                'pass_rate'         => $totalAttempts > 0
                    ? round(($approved / $totalAttempts) * 100, 1)
                    : 0,
                'average_score'     => round($averageScore ?? 0, 2),
                'best_score'        => round($bestScore ?? 0, 2),

                // Estadísticas mejoradas
                'resultado_mas_comun'  => $resultadoMasComun,
                'rango_tipico'         => $rangoTipico,
                'nivel_consistencia'   => $consistency,
                'interpretacion_consistencia' => StatsHelper::consistencyInterpretation($consistency),

                // Comparación anónima con el grupo
                'comparacion_grupo' => [
                    'mi_promedio'     => round($averageScore ?? 0, 2),
                    'promedio_grupo'  => round($promedioGrupo, 2),
                    'diferencia'      => round(($averageScore ?? 0) - $promedioGrupo, 2),
                    'interpretacion'  => ($averageScore ?? 0) >= $promedioGrupo
                        ? 'Tu promedio está por encima de la media del grupo. ¡Sigue así!'
                        : 'Tu promedio está por debajo de la media del grupo. Con práctica lo superarás.',
                ],

                'last_evaluation'   => $lastEval,
                'hardest_step'      => $hardestStep,
                'progress'          => $progress,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/evaluations/analytics
    //  Análisis avanzado del aprendiz autenticado.
    // ──────────────────────────────────────────────────────────────
    public function analytics(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $base   = EppEvaluation::forUser($userId);

        $totalAttempts = (clone $base)->count();

        if ($totalAttempts === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Aún no tienes evaluaciones registradas.',
                'data'    => null,
            ]);
        }

        // ── Estadísticas personales ──────────────────────────────────
        $myScores = (clone $base)
            ->pluck('general_score')
            ->map(fn($s) => (float) $s)
            ->all();

        $promedio         = round(StatsHelper::mean($myScores), 2);
        $mejorPuntaje     = round(max($myScores), 2);
        $resultadoMasComun = StatsHelper::mode($myScores);
        $rangoDelta       = StatsHelper::typicalRangeDelta($myScores);
        $consistency      = StatsHelper::consistencyLevel($myScores);

        // ── Comparación con el grupo ─────────────────────────────────
        $allScores = EppEvaluation::pluck('general_score')
            ->map(fn($s) => (float) $s)
            ->all();

        $promedioGrupo = round(StatsHelper::mean($allScores), 2);
        $mejorGrupo    = round(max($allScores), 2);
        $topPercent    = StatsHelper::topPercent($allScores, $promedio);
        $diferencia    = round($promedio - $promedioGrupo, 2);

        // Puntaje para top 10 % (percentil 90)
        sort($allScores);
        $top10Idx   = (int) ceil(count($allScores) * 0.9) - 1;
        $paraTop10  = isset($allScores[$top10Idx]) ? round($allScores[$top10Idx], 1) : $mejorGrupo;

        // ── Fortalezas y debilidades por paso ───────────────────────
        $pasos = EppStepEvaluation::whereHas(
            'evaluation',
            fn($q) => $q->where('user_id', $userId)
        )
            ->selectRaw('step_number, step_name, AVG(score) * 100 as avg_score')
            ->groupBy('step_number', 'step_name')
            ->orderBy('step_number')
            ->get();

        // Promedios del grupo por paso
        $pasosGrupo = EppStepEvaluation::selectRaw(
            'step_number, AVG(score) * 100 as avg_grupo'
        )
            ->groupBy('step_number')
            ->pluck('avg_grupo', 'step_number');

        $fortalezas = $pasos
            ->filter(fn($p) => (float) $p->avg_score >= 80)
            ->map(fn($p) => [
                'paso'     => $p->step_number,
                'nombre'   => $p->step_name,
                'promedio' => round((float) $p->avg_score, 1),
                'estrellas' => StatsHelper::stars((float) $p->avg_score),
            ])
            ->values();

        $debilidades = $pasos
            ->filter(fn($p) => (float) $p->avg_score < 70)
            ->sortBy('avg_score')
            ->map(function ($p) use ($pasosGrupo) {
                $avgGrupo  = round((float) ($pasosGrupo[$p->step_number] ?? 0), 1);
                $myAvg     = round((float) $p->avg_score, 1);
                return [
                    'paso'           => $p->step_number,
                    'nombre'         => $p->step_name,
                    'promedio'       => $myAvg,
                    'promedio_grupo' => $avgGrupo,
                    'diferencia'     => round($myAvg - $avgGrupo, 1),
                    'recomendacion'  => "El Paso {$p->step_number} ({$p->step_name}) es tu área más débil. "
                        . 'Repasa el procedimiento y practica repetidamente hasta automatizarlo.',
                ];
            })
            ->values();

        // ── Progreso temporal (últimos 10 intentos) ──────────────────
        $historial = (clone $base)
            ->recent()
            ->limit(10)
            ->get(['id', 'general_score', 'created_at'])
            ->reverse()
            ->values()
            ->map(fn($e, $i) => [
                'intento' => $i + 1,
                'puntaje' => (float) $e->general_score,
                'fecha'   => $e->created_at->toDateString(),
            ]);

        // ── Tendencia ─────────────────────────────────────────────────
        $scoresSerie   = $historial->pluck('puntaje')->all();
        $tendencia     = StatsHelper::detectTrend($scoresSerie);
        $pendiente     = StatsHelper::trendSlope($scoresSerie);
        $primerPuntaje = !empty($scoresSerie) ? $scoresSerie[0] : $promedio;
        $mejoraTotal   = round($promedio - $primerPuntaje, 1);

        return response()->json([
            'success' => true,
            'data'    => [
                'personal_stats' => [
                    'promedio'             => $promedio,
                    'mejor_puntaje'        => $mejorPuntaje,
                    'resultado_mas_comun'  => $resultadoMasComun,
                    'rango_tipico'         => $rangoDelta,
                    'nivel_consistencia'   => $consistency,
                    'interpretacion'       => StatsHelper::consistencyInterpretation($consistency),
                    'total_intentos'       => $totalAttempts,
                ],

                'comparacion_grupo' => [
                    'mi_promedio'       => $promedio,
                    'promedio_grupo'    => $promedioGrupo,
                    'diferencia'        => $diferencia,
                    'posicion_estimada' => $topPercent,
                    'mejor_del_grupo'   => $mejorGrupo,
                    'para_top_10'       => $paraTop10,
                ],

                'fortalezas_debilidades' => [
                    'fortalezas'  => $fortalezas,
                    'debilidades' => $debilidades,
                ],

                'progreso_temporal' => $historial,

                'tendencia' => [
                    'tipo'           => $tendencia,
                    'mejora_total'   => $mejoraTotal,
                    'velocidad_mejora' => $pendiente,
                    'interpretacion' => match ($tendencia) {
                        'positiva' => "¡Vas mejorando! Has avanzado {$mejoraTotal} puntos desde tu primer intento.",
                        'negativa' => 'Tus puntajes recientes han bajado un poco. No te desanimes, revisa las áreas débiles.',
                        default    => 'Tus puntajes se mantienen estables. Trabaja en tus debilidades para seguir avanzando.',
                    },
                ],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/aprendices
    //  Lista de aprendices que tienen al menos una evaluación, con
    //  sus estadísticas resumidas.
    // ──────────────────────────────────────────────────────────────
    public function aprendices(): JsonResponse
    {
        $aprendices = User::role('aprendiz')
            ->whereHas('evaluations')
            ->withCount('evaluations')
            ->with([
                'evaluations' => fn($q) => $q
                    ->selectRaw('user_id, AVG(general_score) as avg_score, MAX(general_score) as best_score, MAX(created_at) as last_eval')
                    ->groupBy('user_id'),
            ])
            ->get()
            ->map(function (User $user) {
                $eval = $user->evaluations->first();
                return [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'username'          => $user->username ?? null,
                    'avatar_url'        => $user->avatar_url ?? null,
                    'evaluations_count' => $user->evaluations_count,
                    'avg_score'         => $eval ? round((float) $eval->avg_score, 2) : null,
                    'best_score'        => $eval ? round((float) $eval->best_score, 2) : null,
                    'last_evaluation'   => $eval?->last_eval,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $aprendices,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/aprendices/{aprendizId}/evaluations
    //  Historial paginado de un aprendiz específico para que el
    //  instructor lo revise y pueda dejar comentarios.
    // ──────────────────────────────────────────────────────────────
    public function historyForAprendiz(Request $request, int $aprendizId): JsonResponse
    {
        // Verificar que el usuario existe y tiene rol aprendiz.
        $aprendiz = User::role('aprendiz')->findOrFail($aprendizId);

        $evaluations = EppEvaluation::forUser($aprendizId)
            ->with([
                'steps',
                'errors',
                'comments' => fn($q) => $q->where('instructor_id', $request->user()->id),
            ])
            ->recent()
            ->paginate(10);

        return response()->json([
            'success'  => true,
            'aprendiz' => [
                'id'       => $aprendiz->id,
                'name'     => $aprendiz->name,
                'username' => $aprendiz->username ?? null,
            ],
            'data' => $evaluations,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helper: calcula el status según los datos
    // ──────────────────────────────────────────────────────────────
    private function resolveStatus(array $data): string
    {
        if ($data['steps_completed'] < $data['total_steps']) {
            return 'incompleto';
        }

        return ($data['general_score'] >= 70 && $data['correct_order'])
            ? 'aprobado'
            : 'reprobado';
    }
}
