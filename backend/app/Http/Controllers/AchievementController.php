<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AchievementController extends Controller
{
    /**
     * Lista conquistas do usuário logado.
     */
    public function myAchievements(Request $request)
    {
        return response()->json($request->user()->achievements);
    }

    /**
     * Lista todas as conquistas disponíveis.
     */
    public function index()
    {
        return response()->json(Achievement::all());
    }

    /**
     * Verifica e concede conquistas (Hook operacional).
     * Pode ser chamado após check-in ou compra.
     */
    public function checkAchievements(User $user)
    {
        $achievements = Achievement::all();
        $earned = [];

        foreach ($achievements as $achievement) {
            // Se já tem, ignora
            if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                continue;
            }

            $shouldEarn = false;

            switch ($achievement->type) {
                case 'checkin_count':
                    if ($user->checkins()->count() >= $achievement->required_value) {
                        $shouldEarn = true;
                    }
                    break;
                case 'workout_count':
                    if ($user->workouts()->count() >= $achievement->required_value) {
                        $shouldEarn = true;
                    }
                    break;
            }

            if ($shouldEarn) {
                $user->achievements()->attach($achievement->id, ['earned_at' => Carbon::now()]);
                $earned[] = $achievement;
            }
        }

        return $earned;
    }
}
