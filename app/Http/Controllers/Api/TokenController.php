<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    /**
     * Create a new API token for the authenticated user.
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revocar tokens existentes para evitar acumulación
        $user->tokens()->where('name', 'web-token')->delete();
        
        // Crear nuevo token
        $token = $user->createToken('web-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}
