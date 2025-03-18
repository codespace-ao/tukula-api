<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['error' => 'Link de verificação inválido'], 400);
        }

        if ($user->markEmailAsVerified()) {
            // Gera o token após a verificação
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'E-mail verificado com sucesso',
                'token' => $token,
                'user' => $user,
            ], 200);
        }

        return response()->json(['error' => 'Erro ao verificar o e-mail'], 500);
    }

    public function register(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Erro de validação',
                'messages' => $validator->errors(),
            ], 422);
        }

        // Criação do usuário com e-mail não verificado
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Envia o e-mail de verificação
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Usuário registrado com sucesso! Verifique seu e-mail para ativar a conta.',
            'user' => $user,
        ], 201);
    }

    public function getAuthenticatedUser(Request $request)
    {
        // O middleware 'auth:sanctum' já garante que o usuário está autenticado
        $user = $request->user();

        return response()->json([
            'message' => 'Dados do usuário recuperados com sucesso',
            'user' => $user,
        ], 200);
    }

    public function login(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Erro de validação',
                'messages' => $validator->errors(),
            ], 422);
        }

        // Verifica as credenciais
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Credenciais inválidas',
            ], 401);
        }

        // Gera o token para o usuário autenticado
        $user = auth()->user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login bem-sucedido',
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revoga o token atual do usuário autenticado
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout bem-sucedido',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Link de redefinição enviado! ✅'])
            : response()->json(['error' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Senha redefinida com sucesso! 🎉'])
            : response()->json(['error' => __($status)], 400);
    }
}
