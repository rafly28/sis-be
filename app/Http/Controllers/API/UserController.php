<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function destroy($id)
    {
        $authUser = auth()->user();
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => "User $user tidak ditemukan"], 404);
        }

        // Jika admin → boleh hapus semua
        if ($authUser->hasRole('admin')) {
            $user->delete();
            return response()->json(["message' => `User $user berhasil dihapus (oleh $authUser)"]);
        }

        // Jika guru → boleh hapus murid & orang_tua
        if ($authUser->hasRole('guru')) {
            if ($user->hasRole('murid') || $user->hasRole('orang_tua')) {
                $user->delete();
                return response()->json(["message' => `User berhasil dihapus (oleh $authUser)"]);
            } else {
                return response()->json(['error' => 'Guru hanya bisa menghapus murid atau orang tua'], 403);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}