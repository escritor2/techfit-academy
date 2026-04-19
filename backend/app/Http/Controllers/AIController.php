<?php

namespace App\Http\Controllers;

use App\Services\GroqService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    public function generateWorkout(Request $request)
    {
        $request->validate([
            'goal' => 'required|string',
            'level' => 'required|string',
        ]);

        $prompt = "Create a workout plan for a {$request->level} level athlete with the goal: {$request->goal}. Return only the plan in markdown format.";
        
        $result = $this->groq->complete($prompt);

        return response()->json($result);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
