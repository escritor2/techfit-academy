<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use App\Models\Diet;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    protected $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /**
     * Gera dieta com IA.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'goal' => 'required|string',
            'activity_level' => 'required|string',
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'age' => 'required|integer',
        ]);

        $user = $request->user();

        // 1. Cálculo Dinâmico de Macros (Mifflin-St Jeor simplificado)
        $bmr = (10 * $request->weight) + (6.25 * $request->height) - (5 * $request->age);
        
        $activityMultipliers = [
            'Sedentário' => 1.2,
            'Leve'       => 1.375,
            'Moderado'   => 1.55,
            'Intenso'    => 1.725,
        ];
        
        $multiplier = $activityMultipliers[$request->activity_level] ?? 1.375;
        $tdee = $bmr * $multiplier;

        // Ajuste por objetivo
        if ($request->goal === 'Ganho de Massa') {
            $calories = round($tdee + 400);
            $protein = round($request->weight * 2.2);
            $fats = round($request->weight * 0.9);
        } elseif ($request->goal === 'Perda de Peso') {
            $calories = round($tdee - 500);
            $protein = round($request->weight * 2.5); // Proteína alta para preservar massa
            $fats = round($request->weight * 0.7);
        } else {
            $calories = round($tdee);
            $protein = round($request->weight * 1.8);
            $fats = round($request->weight * 0.8);
        }

        $carbs = round(($calories - ($protein * 4) - ($fats * 9)) / 4);

        // 2. Chamada Groq com Fallback
        if (!config('services.groq.key')) {
            $content = $this->generateFallbackDiet($request->goal, $calories, $protein, $carbs, $fats);
        } else {
            $prompt = "Crie um plano alimentar (dieta) detalhado para um indivíduo com os seguintes dados: "
                    . "Idade: {$request->age}, Peso: {$request->weight}kg, Altura: {$request->height}cm, "
                    . "Nível de Atividade: {$request->activity_level}, Objetivo: {$request->goal}. "
                    . "Total de Calorias: {$calories}kcal. "
                    . "Macros: Proteína {$protein}g, Carbos {$carbs}g, Gorduras {$fats}g. "
                    . "Inclua Café da Manhã, Almoço, Lanche e Jantar. "
                    . "Retorne em formato Markdown com emojis. "
                    . "Responda em português brasileiro.";

            try {
                $result = $this->groq->complete($prompt, "Você é um nutricionista esportivo profissional da Techfit Academy.");
                $content = $result['choices'][0]['message']['content'] ?? $this->generateFallbackDiet($request->goal, $calories, $protein, $carbs, $fats);
            } catch (\Exception $e) {
                $content = $this->generateFallbackDiet($request->goal, $calories, $protein, $carbs, $fats);
            }
        }

        $diet = Diet::create([
            'user_id' => $user->id,
            'goal' => $request->goal,
            'daily_calories' => $calories,
            'protein_grams' => $protein,
            'carbs_grams' => $carbs,
            'fats_grams' => $fats,
            'content' => $content,
        ]);

        return response()->json([
            'diet' => $diet,
            'content' => $content
        ]);
    }

    public function latest(Request $request)
    {
        $diet = $request->user()->diets()->latest()->first();
        return response()->json($diet);
    }

    /**
     * Gera uma dieta de demonstração.
     */
    private function generateFallbackDiet($goal, $cal, $p, $c, $f)
    {
        return "## 🍏 Sua Dieta Personalizada (Demo Mode)\n\n"
             . "**Objetivo:** {$goal}\n"
             . "**Total Diário:** {$cal} kcal\n\n"
             . "### 🍳 Café da Manhã\n"
             . "- Ovos mexidos (3 unidades)\n"
             . "- Pão integral (2 fatias)\n"
             . "- Café sem açúcar\n\n"
             . "### 🥗 Almoço\n"
             . "- Arroz integral (150g)\n"
             . "- Feijão (100g)\n"
             . "- Frango grelhado (150g)\n"
             . "- Salada verde à vontade\n\n"
             . "### 🍎 Lanche da Tarde\n"
             . "- Iogurte natural com granola\n"
             . "- Uma fruta (banana ou maçã)\n\n"
             . "### 🍗 Jantar\n"
             . "- Tilápia grelhada (150g)\n"
             . "- Batata doce cozida (100g)\n"
             . "- Brócolis no vapor\n\n"
             . "> 💡 Nota: Configure a `GROQ_API_KEY` no servidor para receber cardápios variados gerados por IA!";
    }
}
