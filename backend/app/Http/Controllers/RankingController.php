<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    /**
     * Retorna o ranking de check-ins (Leaderboard).
     */
    public function leaderboard()
    {
        // Ranking baseado em check-ins totais
        $ranking = User::where('role', 'member')
            ->withCount('checkins')
            ->orderBy('checkins_count', 'desc')
            ->take(10)
            ->get(['id', 'name']);

        return response()->json($ranking);
    }

    /**
     * Ranking mensal.
     */
    public function monthly()
    {
        $month = now()->month;
        $year = now()->year;

        $ranking = User::where('role', 'member')
            ->select('users.id', 'users.name')
            ->withCount(['checkins' => function ($query) use ($month, $year) {
                $query->whereMonth('checked_in_at', $month)
                      ->whereYear('checked_in_at', $year);
            }])
            ->orderBy('checkins_count', 'desc')
            ->take(10)
            ->get();

        return response()->json($ranking);
    }
}
