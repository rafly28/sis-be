<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('username', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('sort') && $request->sort === 'latest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $users = $query->paginate(10); // Default 10 per page

        return response()->json($users);
    }

    public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        $targetUser = User::findOrFail($id);

        if (!in_array($authUser->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($authUser->role === 'guru' && !in_array($targetUser->role, ['murid', 'orangtua'])) {
            return response()->json(['error' => 'Guru hanya bisa edit murid dan orang tua'], 403);
        }

        $validated = $request->validate([
            'name'     => 'string|max:255',
            'email'    => 'email|unique:users,email,' . $targetUser->id,
            'role'     => 'in:admin,guru,murid,orangtua',
            'username' => 'string|unique:users,username,' . $targetUser->id,
            'password' => 'sometimes|min:6',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        $targetUser->update($validated);

        return response()->json(['message' => 'User berhasil diupdate', 'user' => $targetUser]);
    }

    public function destroy($id)
    {
        $authUser = auth()->user();
        $targetUser = User::findOrFail($id);

        if (!in_array($authUser->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($authUser->role === 'guru' && !in_array($targetUser->role, ['murid', 'orangtua'])) {
            return response()->json(['error' => 'Guru hanya bisa menghapus murid dan orang tua'], 403);
        }

        $targetUser->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }

}
