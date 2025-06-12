<?php

namespace App\Http\Controllers;

use App\Models\Murid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MuridController extends Controller
{
    public function index()
    {
        $murids = Murid::with('orangTua')->get();
        return response()->json($murids);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string',
            'jenis_kelamin'=> 'required|in:L,P',
            'agama'         => 'required|string',
            'tempat_lahir'  => 'required|string',
            'tanggal_lahir' => 'required|date',
            'nisn'          => 'required|numeric|unique:murids',
            'nis'           => 'required|numeric|unique:murids',
            'kelas'         => 'required|string',
            'tahun_masuk'   => 'required|numeric',
            'status'        => 'required|string',
            'orang_tua_id'  => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $murid = Murid::create($request->all());

        return response()->json([
            'message' => 'Murid berhasil ditambahkan',
            'data'    => $murid
        ], 201);
    }

    public function show($id)
    {
        $murid = Murid::with('orangTua')->find($id);

        if (!$murid) {
            return response()->json(['error' => 'Data murid tidak ditemukan'], 404);
        }

        $user = auth()->user();

        // Hanya izinkan murid melihat datanya sendiri dan orang tua anaknya
        if (
            ($user->role === 'murid' && $user->id !== $murid->user_id) ||
            ($user->role === 'orangtua' && $user->id !== $murid->orang_tua_id)
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($murid);
    }

    public function update(Request $request, $id)
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json(['error' => 'Data murid tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'          => 'sometimes|string',
            'jenis_kelamin'=> 'sometimes|in:L,P',
            'agama'         => 'sometimes|string',
            'tempat_lahir'  => 'sometimes|string',
            'tanggal_lahir' => 'sometimes|date',
            'nisn'          => 'sometimes|numeric|unique:murids,nisn,' . $id,
            'nis'           => 'sometimes|numeric|unique:murids,nis,' . $id,
            'kelas'         => 'sometimes|string',
            'tahun_masuk'   => 'sometimes|numeric',
            'status'        => 'sometimes|string',
            'orang_tua_id'  => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $murid->update($request->all());

        return response()->json([
            'message' => 'Data murid berhasil diperbarui',
            'data'    => $murid
        ]);
    }

    public function destroy($id)
    {
        $murid = Murid::find($id);

        if (!$murid) {
            return response()->json(['error' => 'Data murid tidak ditemukan'], 404);
        }

        $murid->delete();

        return response()->json(['message' => 'Murid berhasil dihapus']);
    }
}