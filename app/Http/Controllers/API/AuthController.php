<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken()); // invalidasi token yang sedang dipakai
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh(); // Buat token baru dari token lama

            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
            ]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Token tidak valid atau sudah kedaluwarsa'], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'username'     => auth()->user()->username,
            'name'         => auth()->user()->name, 
            'user_email'   => auth()->user()->email,
            'role'         => auth()->user()->role
        ]);
    }


}
