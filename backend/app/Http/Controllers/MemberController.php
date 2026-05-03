<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    /**
     * Lista todos os membros (admin/employee).
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'member');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $members = $query->withCount('checkins')
            ->with(['subscriptions' => function ($q) {
                $q->active()->with('plan:id,name');
            }])
            ->orderBy('name')
            ->get();

        return response()->json($members);
    }

    /**
     * Cadastra novo membro (admin/employee).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'cpf'      => 'nullable|string|unique:users,cpf',
            'age'      => 'nullable|integer|min:10|max:120',
            'password' => 'required|string|min:6',
        ]);

        $member = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'cpf'      => $request->cpf,
            'age'      => $request->age,
            'password' => Hash::make($request->password),
            'role'     => 'member',
        ]);

        return response()->json($member, 201);
    }

    /**
     * Exibe detalhes de um membro.
     */
    public function show(string $id)
    {
        $member = User::where('role', 'member')
            ->withCount('checkins')
            ->with(['subscriptions' => function ($q) {
                $q->latest()->with('plan:id,name,price');
            }])
            ->findOrFail($id);

        return response()->json($member);
    }

    /**
     * Atualiza membro (admin/employee).
     */
    public function update(Request $request, string $id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $member->id,
            'cpf'   => 'nullable|string|unique:users,cpf,' . $member->id,
            'age'   => 'nullable|integer|min:10|max:120',
        ]);

        $member->update($request->only(['name', 'email', 'cpf', 'age']));

        return response()->json($member);
    }

    /**
     * Remove membro (admin).
     */
    public function destroy(string $id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Membro removido com sucesso.']);
    }
}
