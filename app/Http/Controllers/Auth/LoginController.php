<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Redireciona o usuário para o Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Lida com o retorno do Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt('senha_aleatoria'),
                    'email_verified_at' => now(),
                    'picture_url' => $googleUser->avatar
                ]);
            }

            $token = $user->createToken('API Token')->plainTextToken; // Use Laravel Sanctum ou Passport
            return response()->json([
                'message' => 'Login bem-sucedido',
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao autenticar'], 500);
        }
    }
    
}
