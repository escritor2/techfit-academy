<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use App\Models\Workout;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /**
     * Gera treino com IA e salva no banco.
     */
    public function generateWorkout(Request $request)
    {
        $request->validate([
            'goal'  => 'required|string',
            'level' => 'required|string',
        ]);

        $user = $request->user();

        // Verifica se a API key está configurada
        if (!config('services.groq.key')) {
            // Fallback: gera treino de demonstração
            $content = $this->generateFallbackWorkout($request->goal, $request->level);
        } else {
            $prompt = "Crie um plano de treino completo e detalhado para um aluno de nível {$request->level} "
                    . "com o objetivo de {$request->goal}. "
                    . "Inclua exercícios, séries, repetições, descanso e dicas. "
                    . "Retorne em formato Markdown com emojis para cada seção. "
                    . "Responda em português brasileiro.";

            $result = $this->groq->complete($prompt);

            $content = $result['choices'][0]['message']['content'] ?? $this->generateFallbackWorkout($request->goal, $request->level);
        }

        // Salvar no banco
        $workout = Workout::create([
            'user_id' => $user->id,
            'goal'    => $request->goal,
            'level'   => $request->level,
            'content' => $content,
        ]);

        return response()->json([
            'workout' => $workout,
            'content' => $content,
        ]);
    }

    /**
     * Lista treinos do usuário logado.
     */
    public function myWorkouts(Request $request)
    {
        $workouts = $request->user()
            ->workouts()
            ->latest()
            ->take(10)
            ->get();

        return response()->json($workouts);
    }

    /**
     * Gera treino de demonstração quando a API Groq não está configurada.
     */
    private function generateFallbackWorkout(string $goal, string $level): string
    {
        $workouts = [
            'Hipertrofia' => [
                'Iniciante' => "## 🏋️ Treino de Hipertrofia — Nível Iniciante\n\n### 💪 Treino A — Peito e Tríceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto com Barra | 3 | 12 | 60s |\n| Supino Inclinado Halteres | 3 | 12 | 60s |\n| Crucifixo Máquina | 3 | 15 | 45s |\n| Tríceps Pulley | 3 | 12 | 45s |\n| Tríceps Testa | 3 | 12 | 45s |\n\n### 🦵 Treino B — Costas e Bíceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Puxada Frontal | 3 | 12 | 60s |\n| Remada Baixa | 3 | 12 | 60s |\n| Remada Unilateral | 3 | 12 | 45s |\n| Rosca Direta | 3 | 12 | 45s |\n| Rosca Martelo | 3 | 12 | 45s |\n\n### 🦿 Treino C — Pernas e Ombros\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Leg Press 45° | 3 | 15 | 90s |\n| Cadeira Extensora | 3 | 12 | 60s |\n| Mesa Flexora | 3 | 12 | 60s |\n| Desenvolvimento Máquina | 3 | 12 | 60s |\n| Elevação Lateral | 3 | 15 | 45s |\n\n> 💡 **Dica:** Mantenha a cadência 2-0-2 e aumente a carga progressivamente a cada semana.",
                'Intermediário' => "## 🏋️ Treino de Hipertrofia — Nível Intermediário\n\n### 💪 Treino A — Peito, Ombro e Tríceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto | 4 | 10 | 90s |\n| Supino Inclinado | 4 | 10 | 75s |\n| Crucifixo com Halteres | 3 | 12 | 60s |\n| Desenvolvimento Arnold | 4 | 10 | 75s |\n| Elevação Lateral | 4 | 12 | 45s |\n| Tríceps Francês | 3 | 12 | 60s |\n| Tríceps Corda | 3 | 15 | 45s |\n\n### 🦵 Treino B — Costas e Bíceps\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Barra Fixa | 4 | 8-10 | 90s |\n| Remada Curvada | 4 | 10 | 75s |\n| Puxada Supinada | 3 | 12 | 60s |\n| Remada Cavalinho | 3 | 10 | 60s |\n| Rosca Scott | 4 | 10 | 60s |\n| Rosca Concentrada | 3 | 12 | 45s |\n\n### 🦿 Treino C — Pernas Completo\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Agachamento Livre | 4 | 10 | 120s |\n| Leg Press | 4 | 12 | 90s |\n| Cadeira Extensora | 3 | 15 | 60s |\n| Mesa Flexora | 3 | 12 | 60s |\n| Stiff | 3 | 10 | 75s |\n| Panturrilha em Pé | 4 | 20 | 45s |\n\n> 💡 **Dica:** Use técnicas de drop-set nas últimas séries para intensificar.",
                'Avançado' => "## 🏋️ Treino de Hipertrofia — Nível Avançado\n\n### 💪 Treino A — Peito e Tríceps (Heavy Day)\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Supino Reto Barra | 5 | 6-8 | 120s |\n| Supino Inclinado Halteres | 4 | 8-10 | 90s |\n| Cross Over | 4 | 12 | 60s |\n| Mergulho em Paralelas | 4 | 8-10 | 90s |\n| Tríceps Testa + Supino Fechado (Bi-set) | 4 | 10+8 | 90s |\n\n### 🦵 Treino B — Costas e Bíceps (Volume)\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Barra Fixa com Peso | 5 | 6-8 | 120s |\n| Remada Curvada Pronada | 4 | 8-10 | 90s |\n| Puxada Unilateral | 4 | 10 | 60s |\n| Pull-over | 3 | 12 | 60s |\n| Rosca Direta 21s | 4 | 21 | 75s |\n| Rosca Inversa | 3 | 12 | 45s |\n\n### 🦿 Treino C — Pernas (Destruição Total)\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Agachamento Livre | 5 | 6-8 | 150s |\n| Hack Squat | 4 | 10 | 90s |\n| Extensora (Drop-set) | 4 | 15-12-10 | 60s |\n| Stiff Romeno | 4 | 10 | 90s |\n| Mesa Flexora | 3 | 12 | 60s |\n| Panturrilha Sentado | 5 | 20 | 30s |\n\n### 🏔 Treino D — Ombros e Trapézio\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Desenvolvimento Militar | 5 | 6-8 | 120s |\n| Elevação Lateral | 5 | 12 | 45s |\n| Elevação Frontal | 3 | 12 | 45s |\n| Face Pull | 4 | 15 | 45s |\n| Encolhimento com Barra | 4 | 12 | 60s |\n\n> 💡 **Dica:** Periodize em mesociclos de 4 semanas. Deload na semana 5.",
            ],
            'Emagrecimento' => [
                'Iniciante' => "## 🔥 Treino de Emagrecimento — Nível Iniciante\n\n### 🏃 Protocolo Cardio + Musculação\n\n**Aquecimento (10 min):** Esteira 5.5 km/h\n\n### Circuito Full Body (3 rodadas)\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Agachamento Livre | 15 | 30s |\n| Flexão de Braços (joelhos) | 12 | 30s |\n| Remada Baixa | 12 | 30s |\n| Prancha Abdominal | 30s | 30s |\n| Polichinelo | 30 | 30s |\n\n**Finalizador:** 20 min LISS na bicicleta\n\n> 💡 **Dica:** Mantenha um déficit calórico de 300-500 kcal e beba 2-3L de água por dia.",
                'Intermediário' => "## 🔥 Treino de Emagrecimento — Nível Intermediário\n\n### 🏃 HIIT + Musculação (4x semana)\n\n**Treino A — Upper Body Circuit (4 rounds)**\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Supino com Halteres | 12 | 20s |\n| Remada com Halteres | 12 | 20s |\n| Desenvolvimento | 12 | 20s |\n| Burpees | 10 | 60s |\n\n**Treino B — Lower Body Circuit (4 rounds)**\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Agachamento Búlgaro | 10/perna | 20s |\n| Stiff | 12 | 20s |\n| Salto no Caixote | 10 | 20s |\n| Mountain Climbers | 20 | 60s |\n\n**HIIT Final:** 8x (30s sprint + 30s caminhada)\n\n> 💡 **Dica:** Combine com jejum intermitente 16:8 para potencializar a queima.",
                'Avançado' => "## 🔥 Treino de Emagrecimento — Nível Avançado\n\n### 🏃 Protocolo EMOM + Complex Training\n\n**EMOM 20 min (Every Minute On the Minute)**\n| Minuto | Exercício | Reps |\n|--------|-----------|------|\n| 1 | Thruster | 10 |\n| 2 | Pull-ups | 10 |\n| 3 | Box Jumps | 12 |\n| 4 | Burpee Over Bar | 8 |\n\n**Finalizador Tabata (4 min):** Assault Bike - 20s all-out / 10s rest x 8\n\n> 💡 **Dica:** Inclua 2 sessões de cardio em jejum na semana para acelerar o metabolismo.",
            ],
            'Resistência' => [
                'Iniciante' => "## ⚡ Treino de Resistência — Nível Iniciante\n\n### 🏃 Progressão Aeróbica + Endurance\n\n**Semana 1-2: Base Building**\n| Dia | Atividade | Duração |\n|-----|-----------|---------|\n| Seg | Caminhada Rápida | 30 min |\n| Ter | Circuito Leve (full body) | 25 min |\n| Qua | Descanso ativo (yoga) | 20 min |\n| Qui | Trote leve | 20 min |\n| Sex | Circuito Leve | 25 min |\n\n### Circuito de Resistência (2 rodadas)\n| Exercício | Reps | Descanso |\n|-----------|------|----------|\n| Agachamento | 20 | 20s |\n| Flexão | 15 | 20s |\n| Abdominal | 20 | 20s |\n| Jumping Jacks | 30 | 20s |\n| Prancha | 30s | 30s |\n\n> 💡 **Dica:** Aumente a duração do trote em 5 min por semana.",
                'Intermediário' => "## ⚡ Treino de Resistência — Nível Intermediário\n\n### 🏃 Endurance + Força\n\n**Treino A — Resistência Muscular**\n| Exercício | Séries | Reps | Descanso |\n|-----------|--------|------|----------|\n| Agachamento | 4 | 20 | 45s |\n| Leg Press | 3 | 25 | 45s |\n| Supino | 4 | 15 | 45s |\n| Remada | 4 | 15 | 45s |\n| Prancha | 3 | 60s | 30s |\n\n**Treino B — Cardio Intervalado**\n- 5 min aquecimento\n- 10x (1 min intenso + 1 min moderado)\n- 5 min volta à calma\n\n> 💡 **Dica:** Trabalhe na zona de FC 70-80% para otimizar a resistência aeróbica.",
                'Avançado' => "## ⚡ Treino de Resistência — Nível Avançado\n\n### 🏃 WOD Style + Running\n\n**Dia 1 — The Murph (Scaled)**\n- 800m corrida\n- 50 Pull-ups\n- 100 Push-ups\n- 150 Squats\n- 800m corrida\n\n**Dia 2 — Tempo Run**\n- 2km aquecimento\n- 5km no limiar\n- 2km desaquecimento\n\n**Dia 3 — MetCon**\n| Exercício | Reps |\n|-----------|------|\n| Deadlift (60% 1RM) | 21-15-9 |\n| Box Jump | 21-15-9 |\n| Toes to Bar | 21-15-9 |\n\n> 💡 **Dica:** Monitore sua variabilidade de frequência cardíaca (HRV) para ajustar a intensidade.",
            ],
        ];

        return $workouts[$goal][$level] ?? "## 🏋️ Treino Personalizado\n\n**Objetivo:** {$goal}\n**Nível:** {$level}\n\n### Treino A\n| Exercício | Séries | Reps |\n|-----------|--------|------|\n| Agachamento | 3 | 12 |\n| Supino Reto | 3 | 12 |\n| Remada Baixa | 3 | 12 |\n| Desenvolvimento | 3 | 12 |\n\n> 💡 Configure a chave GROQ_API_KEY para treinos mais personalizados!";
    }
}
