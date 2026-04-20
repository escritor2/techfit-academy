<?php

namespace App\Http\Controllers;

use App\Models\ClassBooking;
use App\Models\GymClass;
use Illuminate\Http\Request;

class ClassBookingController extends Controller
{
    /**
     * Reserva vaga em uma aula (membro).
     */
    public function store(Request $request)
    {
        $request->validate([
            'gym_class_id' => 'required|exists:gym_classes,id',
        ]);

        $gymClass = GymClass::findOrFail($request->gym_class_id);

        // Verificar se a aula está ativa
        if (!$gymClass->is_active) {
            return response()->json(['message' => 'Esta aula não está disponível.'], 422);
        }

        // Verificar vagas
        if ($gymClass->spotsAvailable() <= 0) {
            return response()->json(['message' => 'Não há vagas disponíveis nesta aula.'], 422);
        }

        // Verificar se já está reservado
        $existing = ClassBooking::where('user_id', $request->user()->id)
            ->where('gym_class_id', $gymClass->id)
            ->where('status', 'booked')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Você já está inscrito nesta aula.'], 409);
        }

        $booking = ClassBooking::create([
            'user_id'      => $request->user()->id,
            'gym_class_id' => $gymClass->id,
            'status'       => 'booked',
        ]);

        return response()->json(
            $booking->load('gymClass:id,name,day_of_week,start_time,end_time'),
            201
        );
    }

    /**
     * Cancela reserva (membro).
     */
    public function cancel(string $id)
    {
        $booking = ClassBooking::where('id', $id)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reserva cancelada com sucesso.']);
    }

    /**
     * Lista reservas do membro logado.
     */
    public function myBookings(Request $request)
    {
        $bookings = ClassBooking::where('user_id', $request->user()->id)
            ->where('status', 'booked')
            ->with('gymClass:id,name,day_of_week,start_time,end_time,capacity')
            ->latest()
            ->get();

        return response()->json($bookings);
    }
}
