<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $accessToken = $user->createToken('authToken')->accessToken;
            // $refreshToken = $user->createToken('refreshToken')->refreshToken;

            return response()->json([
                'access_token' => $accessToken,
                'access_type' => "Bearer",
                // 'refresh_token' => $refreshToken,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
}
