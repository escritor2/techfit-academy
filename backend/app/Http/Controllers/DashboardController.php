<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sale;
use App\Models\Checkin;
use App\Models\Subscription;
use App\Models\GymClass;
use App\Models\Workout;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard do Admin — Métricas completas do sistema.
     */
    public function getAdminData(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $activeMembers = User::where('role', 'member')->count();

        $monthlyRevenue = Sale::where('status', 'paid')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total_price');

        $totalSales = Sale::where('status', 'paid')->count();

        $todayCheckins = Checkin::whereDate('checked_in_at', $today)->count();

        $activePlans = Subscription::active()->count();

        $totalClasses = GymClass::where('is_active', true)->count();

        $recentRegistrations = User::where('role', 'member')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Membros com plano vencido
        $expiredCount = User::where('role', 'member')
            ->whereDoesntHave('subscriptions', function ($q) {
                $q->active();
            })
            ->count();

        return response()->json([
            'active_members'       => $activeMembers,
            'monthly_revenue'      => $monthlyRevenue,
            'total_sales'          => $totalSales,
            'today_checkins'       => $todayCheckins,
            'active_plans'         => $activePlans,
            'total_classes'        => $totalClasses,
            'expired_members'      => $expiredCount,
            'recent_registrations' => $recentRegistrations,
        ]);
    }

    /**
     * Dashboard do Membro — Dados pessoais.
     */
    public function getMemberData(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $recentPurchases = Sale::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Subscription ativa
        $subscription = $user->subscriptions()
            ->active()
            ->with('plan')
            ->first();

        // Último treino gerado
        $latestWorkout = $user->workouts()->first();

        // Check-ins do mês
        $monthlyCheckins = Checkin::where('user_id', $user->id)
            ->whereMonth('checked_in_at', $now->month)
            ->whereYear('checked_in_at', $now->year)
            ->count();

        // Aulas reservadas
        $upcomingBookings = $user->classBookings()
            ->where('status', 'booked')
            ->with('gymClass')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'recent_purchases'  => $recentPurchases,
            'subscription'      => $subscription,
            'latest_workout'    => $latestWorkout,
            'monthly_checkins'  => $monthlyCheckins,
            'upcoming_bookings' => $upcomingBookings,
        ]);
    }

    /**
     * Dashboard do Funcionário — Dados operacionais.
     */
    public function getEmployeeData(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['employee', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        // Check-ins de hoje
        $todayCheckins = Checkin::whereDate('checked_in_at', $today)
            ->with('user:id,name,cpf,email')
            ->orderBy('checked_in_at', 'desc')
            ->get();

        // Membros com plano vencido
        $expiredMembers = User::where('role', 'member')
            ->whereDoesntHave('subscriptions', function ($q) {
                $q->active();
            })
            ->select('id', 'name', 'cpf', 'email')
            ->take(10)
            ->get();

        // Vendas do dia
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total_price');

        $totalMembersToday = $todayCheckins->count();

        return response()->json([
            'today_checkins'   => $todayCheckins,
            'expired_members'  => $expiredMembers,
            'today_sales'      => $todaySales,
            'total_entries'    => $totalMembersToday,
        ]);
    }
}
