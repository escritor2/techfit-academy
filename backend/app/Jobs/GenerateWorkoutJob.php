<?php

namespace App\Jobs;

use App\Models\Workout;
use App\Services\GroqService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateWorkoutJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $userId,
        public readonly int $workoutId,
        public readonly string $goal,
        public readonly string $level,
    ) {}

    /**
     * Execute o job: chama a IA e atualiza o workout salvo no banco.
     */
    public function handle(GroqService $groq): void
    {
        $workout = Workout::find($this->workoutId);
        if (!$workout) {
            return;
        }

        try {
            $prompt = "Crie um plano de treino completo e detalhado para um aluno de nível {$this->level} "
                    . "com o objetivo de {$this->goal}. "
                    . "Inclua exercícios, séries, repetições, descanso e dicas. "
                    . "Retorne em formato Markdown com emojis para cada seção. "
                    . "Responda em português brasileiro.";

            $result = $groq->complete($prompt);
            $content = $result['choices'][0]['message']['content'] ?? null;

            if ($content) {
                $workout->update([
                    'content' => $content,
                    'status'  => 'ready',
                ]);
            } else {
                $workout->update(['status' => 'fallback']);
            }
        } catch (\Throwable $e) {
            Log::error('GenerateWorkoutJob falhou', ['error' => $e->getMessage(), 'workout_id' => $this->workoutId]);
            $workout->update(['status' => 'error']);
        }
    }

    public function failed(\Throwable $e): void
    {
        $workout = Workout::find($this->workoutId);
        $workout?->update(['status' => 'error']);
        Log::error('GenerateWorkoutJob falhou definitivamente', ['error' => $e->getMessage()]);
    }
}
