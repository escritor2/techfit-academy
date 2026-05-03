<?php

namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    /**
     * Lista todas as aulas ativas (público).
     */
    public function index()
    {
        \Log::info('GymClassController: Index called');
        $classes = GymClass::with('instructor:id,name')
            ->where('is_active', true)
            ->withCount(['activeBookings'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(function ($class) {
                $class->spots_available = $class->capacity - $class->active_bookings_count;
                return $class;
            });

        \Log::info('GymClassController: Query finished, count: ' . $classes->count());
        return response()->json($classes);
    }

    /**
     * Cria nova aula (admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'instructor_id' => 'required|exists:users,id',
            'day_of_week'   => 'required|string',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'capacity'      => 'required|integer|min:1',
        ]);

        $class = GymClass::create(array_merge($request->all(), [
            'schedule' => $request->day_of_week . ' ' . $request->start_time,
            'is_active' => true,
        ]));

        return response()->json($class->load('instructor:id,name'), 201);
    }

    /**
     * Exibe aula específica.
     */
    public function show(string $id)
    {
        $class = GymClass::with(['instructor:id,name', 'activeBookings.user:id,name'])
            ->findOrFail($id);

        $class->spots_available = $class->spotsAvailable();

        return response()->json($class);
    }

    /**
     * Atualiza aula (admin).
     */
    public function update(Request $request, string $id)
    {
        $class = GymClass::findOrFail($id);

        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'instructor_id' => 'sometimes|exists:users,id',
            'day_of_week'   => 'sometimes|string',
            'start_time'    => 'sometimes|date_format:H:i',
            'end_time'      => 'sometimes|date_format:H:i',
            'capacity'      => 'sometimes|integer|min:1',
            'is_active'     => 'sometimes|boolean',
        ]);

        $data = $request->all();
        if ($request->has('day_of_week') || $request->has('start_time')) {
            $data['schedule'] = ($request->day_of_week ?? $class->day_of_week) . ' ' . ($request->start_time ?? $class->start_time);
        }

        $class->update($data);

        return response()->json($class->load('instructor:id,name'));
    }

    /**
     * Remove aula (admin).
     */
    public function destroy(string $id)
    {
        $class = GymClass::findOrFail($id);
        $class->delete();

        return response()->json(['message' => 'Aula removida com sucesso.']);
    }
}
