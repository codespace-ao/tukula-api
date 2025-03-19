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
    public function verifyEmail($id, $hash, Request $request)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string)$hash, sha1($user->getEmailForVerification()))) {
            // Redireciona para o frontend com uma mensagem de erro (ex.: usando query params)
            return redirect()->away(
                env('FRONTEND_URL', 'http://localhost:3000') . '/email-verification?status=error&message=Invalid verification link'
            );
        }

        if ($user->markEmailAsVerified()) {
            $token = $user->createToken('API Token')->plainTextToken;

            // Redireciona para o frontend com o token e mensagem de sucesso
            return redirect()->away(
                env('FRONTEND_URL', 'http://localhost:3000') . '/email-verification?status=success&message=Email verified successfully&token=' . $token
            );
        }

        // Redireciona para o frontend com uma mensagem de erro
        return redirect()->away(
            env('FRONTEND_URL', 'http://localhost:3000') . '/email-verification?status=error&message=Error verifying email'
        );
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully! Verify your email to activate your account.',
            'data' => ['user' => $user]
        ], 201);
    }

    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'message' => 'User data retrieved successfully',
            'data' => ['user' => $user]
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'errors' => ['auth' => ['Email or password is incorrect.']]
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => ['token' => $token]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful',
            'data' => null
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'status' => 'success',
                'message' => 'Reset link sent successfully',
                'data' => null
            ], 200)
            : response()->json([
                'status' => 'error',
                'message' => 'Error sending reset link',
                'errors' => ['email' => [__($status)]]
            ], 400);
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
            ? response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully',
                'data' => null
            ], 200)
            : response()->json([
                'status' => 'error',
                'message' => 'Error resetting password',
                'errors' => ['reset' => [__($status)]]
            ], 400);
    }
}
