<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;

class ProfileController extends Controller
{
    // Atualiza nome do usuário autenticado
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return $this->success($user, 'Perfil atualizado com sucesso!');
    }

    // Altera senha do usuário autenticado
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Senha atual incorreta.', 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->success(null, 'Senha alterada com sucesso!');
    }

    // Envia email de recuperação de senha
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->success(null, 'Email de recuperação enviado! Verifique sua caixa de entrada.');
            }

            return $this->error('Não foi possível enviar o email. Tente novamente.', 422);
        } catch (\Exception $e) {
            return $this->error('Erro ao enviar email: ' . $e->getMessage(), 500);
        }
    }

    // Redefine a senha com o token do email
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete(); // Invalida tokens antigos após reset
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Senha redefinida com sucesso! Faça login com a nova senha.');
        }

        return $this->error('Token inválido ou expirado.', 422);
    }
}
