<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides.',
            ], 422);
        }

        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $user->createToken('pos-mobile')->plainTextToken,
                'user' => $user,
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => request()->user(),
        ]);
    }
}
