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
            $newToken = JWTAuth::parseToken()->refresh();

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

    protected function register(Request $request)
    {
        $request->validate([
        'name'     => 'required|string|max:255',
        'username' => 'required|string|unique:users,username',
        'role'     => 'required|in:admin,guru,murid,orang_tua',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        ]);

        $user = User::create([
        'name'     => $request->name,
        'username' => $request->username,
        'role'     => $request->role,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
        ]);
    }
}
