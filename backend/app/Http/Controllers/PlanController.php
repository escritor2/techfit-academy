<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Lista todos os planos.
     */
    public function index()
    {
        \Log::info('PlanController: Index called');
        $plans = Plan::orderBy('price')->get();
        \Log::info('PlanController: Query finished, count: ' . $plans->count());
        return response()->json($plans);
    }

    /**
     * Cria novo plano (admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
        ]);

        $plan = Plan::create($request->all());

        return response()->json($plan, 201);
    }

    /**
     * Exibe plano específico.
     */
    public function show(string $id)
    {
        return response()->json(Plan::findOrFail($id));
    }

    /**
     * Atualiza plano (admin).
     */
    public function update(Request $request, string $id)
    {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'duration_days' => 'sometimes|integer|min:1',
            'price'         => 'sometimes|numeric|min:0',
            'description'   => 'nullable|string',
            'is_active'     => 'sometimes|boolean',
        ]);

        $plan->update($request->all());

        return response()->json($plan);
    }

    /**
     * Remove plano (admin).
     */
    public function destroy(string $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Plano removido com sucesso.']);
    }
}
