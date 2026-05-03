<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Autentica um usuário e retorna o token e dados do usuário.
     */
    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas são inválidas.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        
        $this->updateLoginStats($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }

    /**
     * Atualiza as estatísticas de login do usuário.
     */
    protected function updateLoginStats(User $user): void
    {
        $today = now()->format('Y-m-d');
        if ($user->last_login_date !== $today) {
            $user->login_days = ($user->login_days ?? 0) + 1;
            $user->last_login_date = $today;
            $user->save();
        }
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
