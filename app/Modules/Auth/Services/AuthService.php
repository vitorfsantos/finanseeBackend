<?php

namespace App\Modules\Auth\Services;

use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate user and generate token
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])
        ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Credenciais inválidas'
            ];
        }

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Logout user and revoke token
     */
    public function logout(User $user): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();

        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ];
    }
}
