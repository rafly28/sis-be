<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
        public function store(Request $request)
    {
        $authUser = auth()->user();

        // Tentukan role yang boleh dipilih sesuai role si pembuat
        if ($authUser->hasRole('admin')) {
            $allowedRoles = ['admin', 'guru', 'murid', 'orang_tua'];
        } elseif ($authUser->hasRole('guru')) {
            $allowedRoles = ['murid', 'orang_tua'];
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|string|in:' . implode(',', $allowedRoles),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'message' => "User berhasil dibuat",
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]
        ], 201);
    }

        public function index()
    {
        $authUser = auth()->user();

        if ($authUser->hasRole('admin')) {
            $users = User::with('roles')->get();
        } elseif ($authUser->hasRole('guru')) {
            $users = User::with('roles')
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['murid', 'orang_tua']))
                ->get();
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($users);
    }

        public function show($id)
    {
        $authUser = auth()->user();
        $user = User::with('roles')->findOrFail($id);

        if ($authUser->hasRole('admin')) {
            return response()->json($user);
        } elseif ($authUser->hasRole('guru') && $user->hasAnyRole(['murid', 'orang_tua'])) {
            return response()->json($user);
        } elseif ($authUser->id === $user->id) {
            return response()->json($user);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

        public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        if (!($authUser->hasRole('admin') || 
            ($authUser->hasRole('guru') && $user->hasAnyRole(['murid','orang_tua'])) ||
            ($authUser->id === $user->id))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'role'     => 'sometimes|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update([
            'name'     => $request->name ?? $user->name,
            'email'    => $request->email ?? $user->email,
            'username' => $request->username ?? $user->username,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'User berhasil diupdate',
            'user'    => $user->load('roles')
        ]);
    }

        public function destroy($id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        if ($authUser->hasRole('admin') || 
            ($authUser->hasRole('guru') && $user->hasAnyRole(['murid', 'orang_tua']))) {
            $user->delete();
            return response()->json(['message' => "User {$user->name} berhasil dihapus"]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

}