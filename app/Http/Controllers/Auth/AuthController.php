<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getAuthenticatedUser(Request $request)
    {
        // O middleware 'auth:sanctum' já garante que o usuário está autenticado
        $user = $request->user();

        return response()->json([
            'message' => 'Dados do usuário recuperados com sucesso',
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Revoga o token atual do usuário autenticado
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout bem-sucedido',
        ], 200);
    }
}
