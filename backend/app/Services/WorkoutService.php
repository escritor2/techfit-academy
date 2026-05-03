<?php

namespace App\Services;

use App\Models\Workout;
use App\Models\User;
use App\Jobs\GenerateWorkoutJob;

class WorkoutService
{
    /**
     * Inicia o processo de geração de treino.
     */
    public function generate(User $user, string $goal, string $level): Workout
    {
        $workout = Workout::create([
            'user_id' => $user->id,
            'goal'    => $goal,
            'level'   => $level,
            'content' => $this->generateFallbackWorkout($goal, $level),
            'status'  => config('services.groq.key') ? 'pending' : 'fallback',
        ]);

        if (config('services.groq.key')) {
            GenerateWorkoutJob::dispatch(
                userId: $user->id,
                workoutId: $workout->id,
                goal: $goal,
                level: $level,
            );
        }

        return $workout;
    }

    /**
     * Obtém os treinos recentes do usuário.
     */
    public function getRecentWorkouts(User $user, int $limit = 10)
    {
        return $user->workouts()
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Obtém o status de um treino específico.
     */
    public function getWorkoutStatus(User $user, int $id): Workout
    {
        return Workout::where('user_id', $user->id)->findOrFail($id);
    }

    /**
     * Gera treino de demonstração (fallback).
     */
    private function generateFallbackWorkout(string $goal, string $level): string
    {
        // Lógica movida do controller para cá
        $workouts = [
            'Hipertrofia' => [
                'Iniciante'     => "## 🏋️ Treino de Hipertrofia — Nível Iniciante\n\n### 💪 Treino A — Peito e Tríceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto com Barra | 3 | 12 | 60s |\n| Supino Inclinado Halteres | 3 | 12 | 60s |\n| Crucifixo Máquina | 3 | 15 | 45s |\n| Tríceps Pulley | 3 | 12 | 45s |\n| Tríceps Testa | 3 | 12 | 45s |\n\n### 🦵 Treino B — Costas e Bíceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Puxada Frontal | 3 | 12 | 60s |\n| Remada Baixa | 3 | 12 | 60s |\n| Remada Unilateral | 3 | 12 | 45s |\n| Rosca Direta | 3 | 12 | 45s |\n| Rosca Martelo | 3 | 12 | 45s |\n\n> 💡 **Dica:** Mantenha a cadência 2-0-2 e aumente a carga progressivamente a cada semana.",
                'Intermediário' => "## 🏋️ Treino de Hipertrofia — Nível Intermediário\n\n### 💪 Treino A — Peito, Ombro e Tríceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto | 4 | 10 | 90s |\n| Supino Inclinado | 4 | 10 | 75s |\n| Crucifixo com Halteres | 3 | 12 | 60s |\n| Desenvolvimento Arnold | 4 | 10 | 75s |\n| Tríceps Francês | 3 | 12 | 60s |\n\n> 💡 **Dica:** Use técnicas de drop-set nas últimas séries para intensificar.",
                'Avançado'      => "## 🏋️ Treino de Hipertrofia — Nível Avançado\n\n### 💪 Treino A — Peito e Tríceps (Heavy Day)\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto Barra | 5 | 6-8 | 120s |\n| Supino Inclinado Halteres | 4 | 8-10 | 90s |\n| Cross Over | 4 | 12 | 60s |\n| Mergulho em Paralelas | 4 | 8-10 | 90s |\n\n> 💡 **Dica:** Periodize em mesociclos de 4 semanas. Deload na semana 5.",
            ],
            'Emagrecimento' => [
                'Iniciante'     => "## 🔥 Treino de Emagrecimento — Nível Iniciante\n\n### 🏃 Circuito Full Body (3 rodadas)\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Agachamento Livre | 15 | 30s |\n| Flexão de Braços | 12 | 30s |\n| Remada Baixa | 12 | 30s |\n| Prancha Abdominal | 30s | 30s |\n\n**Finalizador:** 20 min bicicleta\n\n> 💡 Déficit calórico de 300-500 kcal + 2-3L de água por dia.",
                'Intermediário' => "## 🔥 Treino de Emagrecimento — Nível Intermediário\n\n### 🏃 HIIT + Musculação (4x semana)\n\n**Treino A — Upper Body Circuit (4 rounds)**\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Supino com Halteres | 12 | 20s |\n| Remada com Halteres | 12 | 20s |\n| Burpees | 10 | 60s |\n\n**HIIT Final:** 8x (30s sprint + 30s caminhada)\n\n> 💡 Combine com jejum intermitente 16:8.",
                'Avançado'      => "## 🔥 Treino de Emagrecimento — Nível Avançado\n\n### EMOM 20 min\n| Minuto | Exercício | Reps |\n|--------|-----------|------|\n| 1 | Thruster | 10 |\n| 2 | Pull-ups | 10 |\n| 3 | Box Jumps | 12 |\n| 4 | Burpee Over Bar | 8 |\n\n**Finalizador Tabata:** 20s all-out / 10s rest x 8",
            ],
            'Resistência' => [
                'Iniciante'     => "## ⚡ Treino de Resistência — Nível Iniciante\n\n### Circuito de Resistência (2 rodadas)\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Agachamento | 20 | 20s |\n| Flexão | 15 | 20s |\n| Jumping Jacks | 30 | 20s |\n| Prancha | 30s | 30s |\n\n> 💡 Aumente o trote em 5 min por semana.",
                'Intermediário' => "## ⚡ Treino de Resistência — Nível Intermediário\n\n**Treino A — Resistência Muscular**\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Agachamento | 4 | 20 | 45s |\n| Supino | 4 | 15 | 45s |\n| Prancha | 3 | 60s | 30s |\n\n**Treino B:** 10x (1 min intenso + 1 min moderado)",
                'Avançado'      => "## ⚡ Treino de Resistência — Nível Avançado\n\n**The Murph (Scaled)**\n- 800m corrida → 50 Pull-ups → 100 Push-ups → 150 Squats → 800m corrida\n\n**Tempo Run:** 2km aquecimento + 5km no limiar + 2km desaquecimento",
            ],
        ];

        return $workouts[$goal][$level] ?? "Treino não encontrado para os parâmetros informados.";
    }
}
