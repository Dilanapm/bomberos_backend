<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInstructorCommentRequest;
use App\Models\EppEvaluation;
use App\Models\EppInstructorComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorCommentController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  POST /api/v1/instructor/evaluations/{id}/comments
    //  Agrega un comentario a una evaluación.
    // ──────────────────────────────────────────────────────────────
    public function store(StoreInstructorCommentRequest $request, int $evaluationId): JsonResponse
    {
        // Verifica que la evaluación exista
        $evaluation = EppEvaluation::findOrFail($evaluationId);

        $comment = EppInstructorComment::create([
            'evaluation_id' => $evaluation->id,
            'instructor_id' => $request->user()->id,
            'step_number'   => $request->step_number,
            'comment'       => $request->comment,
            'type'          => $request->type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario agregado correctamente.',
            'data'    => $comment->load('instructor:id,name,username,avatar'),
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET /api/v1/instructor/evaluations/{id}/comments
    //  Lista comentarios de una evaluación (con filtro opcional por tipo/paso).
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request, int $evaluationId): JsonResponse
    {
        EppEvaluation::findOrFail($evaluationId);

        $query = EppInstructorComment::with('instructor:id,name,username,avatar')
            ->where('evaluation_id', $evaluationId)
            ->orderBy('created_at', 'desc');

        if ($request->has('step_number')) {
            $query->where('step_number', $request->integer('step_number'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->string('type'));
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  DELETE /api/v1/instructor/evaluations/{evaluationId}/comments/{commentId}
    //  Solo el instructor que creó el comentario puede eliminarlo.
    // ──────────────────────────────────────────────────────────────
    public function destroy(Request $request, int $evaluationId, int $commentId): JsonResponse
    {
        $comment = EppInstructorComment::where('id', $commentId)
            ->where('evaluation_id', $evaluationId)
            ->where('instructor_id', $request->user()->id)
            ->firstOrFail();

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comentario eliminado.',
        ]);
    }
}
