<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MuridController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Logic Login dan crud user login
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->post('/refresh', [AuthController::class, 'refresh']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/users', [UserController::class, 'index']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
// Middleware Role
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Hello Admin']);
    });
});

Route::middleware(['auth:api', 'role:guru'])->group(function () {
    Route::get('/guru-only', function () {
        return response()->json(['message' => 'Hello Guru']);
    });
});

// Logic Murid
Route::middleware(['auth:api'])->group(function () {
    Route::get('/murids', [MuridController::class, 'index'])->middleware('role:admin,guru');
    Route::post('/murids', [MuridController::class, 'store'])->middleware('role:admin,guru');
    Route::get('/murids/{id}', [MuridController::class, 'show'])->middleware('role:admin,guru,orangtua,murid');
    Route::put('/murids/{id}', [MuridController::class, 'update'])->middleware('role:admin,guru');
    Route::delete('/murids/{id}', [MuridController::class, 'destroy'])->middleware('role:admin,guru');
});

