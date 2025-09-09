<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;

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
// Old Logic Login
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
// Route::middleware('auth:api')->post('/refresh', [AuthController::class, 'refresh']);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout',   [AuthController::class, 'logout']);
    Route::post('/refresh',  [AuthController::class, 'refresh']);

    // Contoh route untuk cek user login + role/permission
    // Route::get('/me', function () {
    //     $user = auth()->user();
    //     return response()->json([
    //         'user'        => $user->only(['id','name','username','email']),
    //         'roles'       => $user->getRoleNames(),
    //         'permissions' => $user->getAllPermissions()->pluck('name'),
    //     ]);
    // });
});

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

// CRUD Users
// Route::middleware(['auth:api'])->group(function () {
//     Route::delete('/users/{id}', [UserController::class, 'destroy']);
// });
Route::middleware('auth:api')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

