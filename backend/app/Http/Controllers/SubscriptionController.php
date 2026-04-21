<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Lista assinaturas (admin/employee).
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user:id,name,email,cpf', 'plan:id,name,price']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Cria assinatura para um membro (admin/employee).
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $plan = Plan::findOrFail($request->plan_id);

        // Cancelar subscription ativa anterior
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $subscription = Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'starts_at'  => Carbon::today(),
            'expires_at' => Carbon::today()->addDays($plan->duration_days),
            'status'     => 'active',
        ]);

        return response()->json(
            $subscription->load(['user:id,name,email', 'plan:id,name,price']),
            201
        );
    }

    /**
     * Exibe assinatura específica.
     */
    public function show(string $id)
    {
        return response()->json(
            Subscription::with(['user:id,name,email', 'plan:id,name,price'])->findOrFail($id)
        );
    }

    /**
     * Cancela assinatura.
     */
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();
        return response()->json(['message' => 'Assinatura removida.']);
    }

    /**
     * Checkout simulado para membros.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
        ]);

        $user = $request->user();
        $plan = \App\Models\Plan::find($request->plan_id);

        // Cancela assinaturas ativas anteriores (opcional, dependendo da regra)
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        // Cria nova assinatura
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
            'status' => 'active',
            'price_paid' => $plan->price,
        ]);

        return response()->json([
            'message' => 'Pagamento aprovado! Seu plano está ativo.',
            'subscription' => $subscription
        ]);
    }
}
