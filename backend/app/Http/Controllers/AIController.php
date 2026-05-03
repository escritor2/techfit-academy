<?php

namespace App\Http\Controllers;

use App\Services\WorkoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    protected $workoutService;

    public function __construct(WorkoutService $workoutService)
    {
        $this->workoutService = $workoutService;
    }

    public function generateWorkout(Request $request): JsonResponse
    {        $request->validate([
            'goal'  => 'required|string',
            'level' => 'required|string',
        ]);

        $workout = $this->workoutService->generate(
            $request->user(),
            $request->goal,
            $request->level
        );

        return $this->success([
            'workout' => $workout,
            'queued'  => config('services.groq.key') ? true : false,
        ], config('services.groq.key') 
            ? 'Treino sendo gerado pela IA. Pronto em alguns segundos!' 
            : 'Treino gerado com template local.', 201);
    }

    public function myWorkouts(Request $request): JsonResponse
    {        $workouts = $this->workoutService->getRecentWorkouts($request->user());
        return $this->success($workouts);
    }

    public function workoutStatus(Request $request, int $id): JsonResponse
    {        $workout = $this->workoutService->getWorkoutStatus($request->user(), $id);

        return $this->success([
            'id'      => $workout->id,
            'status'  => $workout->status,
            'content' => $workout->isReady() || $workout->status === 'fallback' ? $workout->content : null,
        ]);
    }
}
