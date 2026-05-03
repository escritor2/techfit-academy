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
            return $this->unauthorized('Acesso negado.');
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $data = [
            'active_members'       => User::where('role', 'member')->count(),
            'monthly_revenue'      => Sale::where('status', 'paid')
                                        ->whereMonth('created_at', $now->month)
                                        ->whereYear('created_at', $now->year)
                                        ->sum('total_price'),
            'total_sales'          => Sale::where('status', 'paid')->count(),
            'today_checkins'       => Checkin::whereDate('checked_in_at', $today)->count(),
            'active_plans'         => Subscription::active()->count(),
            'total_classes'        => GymClass::where('is_active', true)->count(),
            'expired_members'      => User::where('role', 'member')
                                        ->whereDoesntHave('subscriptions', function ($q) {
                                            $q->active();
                                        })->count(),
            'recent_registrations' => User::where('role', 'member')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get(),
        ];

        return $this->success($data);
    }

    /**
     * Dashboard do Membro — Dados pessoais.
     */
    public function getMemberData(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $data = [
            'recent_purchases' => Sale::where('user_id', $user->id)
                                    ->with('items.product')
                                    ->orderBy('created_at', 'desc')
                                    ->get(),
            'subscription'     => $user->subscriptions()
                                    ->active()
                                    ->with('plan')
                                    ->first(),
            'latest_workout'   => $user->workouts()->first(),
            'monthly_checkins' => Checkin::where('user_id', $user->id)
                                    ->whereMonth('checked_in_at', $now->month)
                                    ->whereYear('checked_in_at', $now->year)
                                    ->count(),
            'upcoming_bookings' => $user->classBookings()
                                    ->where('status', 'booked')
                                    ->with('gymClass')
                                    ->latest()
                                    ->take(5)
                                    ->get(),
        ];

        return $this->success($data);
    }

    /**
     * Dashboard do Funcionário — Dados operacionais.
     */
    public function getEmployeeData(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['employee', 'admin'])) {
            return $this->unauthorized('Acesso negado.');
        }

        $today = Carbon::today();

        $data = [
            'today_checkins'   => Checkin::whereDate('checked_in_at', $today)
                                    ->with('user:id,name,cpf,email')
                                    ->orderBy('checked_in_at', 'desc')
                                    ->get(),
            'expired_members'  => User::where('role', 'member')
                                    ->whereDoesntHave('subscriptions', function ($q) {
                                        $q->active();
                                    })
                                    ->select('id', 'name', 'cpf', 'email')
                                    ->take(10)
                                    ->get(),
            'today_sales'      => Sale::whereDate('created_at', $today)
                                    ->where('status', 'paid')
                                    ->sum('total_price'),
        ];

        $data['total_entries'] = $data['today_checkins']->count();

        return $this->success($data);
    }
}
