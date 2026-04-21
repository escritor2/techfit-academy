<?php

namespace App\Http\Controllers;

use App\Models\BodyMetric;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MetricsController extends Controller
{
    /**
     * Lista métricas do usuário logado.
     */
    public function index(Request $request)
    {
        $metrics = $request->user()->bodyMetrics;
        return response()->json($metrics);
    }

    /**
     * Salva nova medição.
     */
    public function store(Request $request)
    {
        $request->validate([
            'weight'      => 'required|numeric|min:20',
            'height'      => 'nullable|numeric|min:50',
            'body_fat'    => 'nullable|numeric|min:0|max:100',
            'chest'       => 'nullable|numeric',
            'waist'       => 'nullable|numeric',
            'biceps'      => 'nullable|numeric',
            'thigh'       => 'nullable|numeric',
            'measured_at' => 'nullable|date',
        ]);

        $metric = $request->user()->bodyMetrics()->create(array_merge($request->all(), [
            'measured_at' => $request->measured_at ?? Carbon::today(),
        ]));

        return response()->json($metric, 201);
    }

    /**
     * Dados para o gráfico de evolução.
     */
    public function history(Request $request)
    {
        $metrics = $request->user()->bodyMetrics()
            ->orderBy('measured_at', 'asc')
            ->get(['weight', 'body_fat', 'measured_at']);

        return response()->json($metrics);
    }
}
