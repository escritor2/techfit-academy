<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /**
     * Registra check-in de membro via CPF (employee/admin).
     */
    public function store(Request $request)
    {
        $employee = $request->user();
        if (!in_array($employee->role, ['employee', 'admin'])) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $request->validate([
            'cpf' => 'required|string',
        ]);

        // Buscar membro pelo CPF
        $member = User::where('cpf', $request->cpf)
            ->where('role', 'member')
            ->first();

        if (!$member) {
            return response()->json(['message' => 'Membro não encontrado com esse CPF.'], 404);
        }

        // Verificar se já fez check-in hoje
        $alreadyCheckedIn = Checkin::where('user_id', $member->id)
            ->whereDate('checked_in_at', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json([
                'message' => 'Este membro já registrou entrada hoje.',
                'member'  => $member->only(['id', 'name', 'email']),
            ], 409);
        }

        // Verificar subscription ativa
        $hasActivePlan = $member->hasActiveSubscription();

        // Registrar check-in
        $checkin = Checkin::create([
            'user_id'       => $member->id,
            'checked_in_by' => $employee->id,
            'checked_in_at' => Carbon::now(),
        ]);

        // Atualizar last_attendance do membro
        $member->update(['last_attendance' => Carbon::now()]);

        return response()->json([
            'message'         => 'Entrada registrada com sucesso!',
            'member'          => $member->only(['id', 'name', 'email', 'cpf']),
            'checkin'         => $checkin,
            'has_active_plan' => $hasActivePlan,
        ], 201);
    }

    /**
     * Lista check-ins de hoje.
     */
    public function todayList(Request $request)
    {
        $employee = $request->user();
        if (!in_array($employee->role, ['employee', 'admin'])) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $checkins = Checkin::whereDate('checked_in_at', Carbon::today())
            ->with('user:id,name,cpf,email')
            ->orderBy('checked_in_at', 'desc')
            ->get();

        return response()->json($checkins);
    }
}
