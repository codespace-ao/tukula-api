<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

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

        // Criação do usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now()
        ]);

        // Gera o token de autenticação
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'token' => $token,
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
}
