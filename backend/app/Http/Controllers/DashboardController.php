<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getAdminData(Request $request)
    {
        // Require admin role
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activeMembers = User::where('role', 'member')->count();
        
        $monthlyRevenue = Sale::where('status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price');

        $totalSales = Sale::where('status', 'paid')->count();

        $recentRegistrations = User::where('role', 'member')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'active_members' => $activeMembers,
            'monthly_revenue' => $monthlyRevenue,
            'total_sales' => $totalSales,
            'recent_registrations' => $recentRegistrations
        ]);
    }

    public function getMemberData(Request $request)
    {
        // Require member role (or admin testing)
        $user = $request->user();
        
        $recentPurchases = Sale::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'recent_purchases' => $recentPurchases
        ]);
    }
}
