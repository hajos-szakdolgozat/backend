<?php

use App\Http\Controllers\BoatController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum'])->group(function () {

    // felhasználók
    Route::prefix('users')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // hajók
    Route::prefix('boats')->group(function () {
        Route::get('/', [BoatController::class, 'index']);
        Route::get('{boat}', [BoatController::class, 'show']);
        Route::post('/', [BoatController::class, 'store']);
        Route::put('{boat}', [BoatController::class, 'update']);
        Route::delete('{boat}', [BoatController::class, 'destroy']);
    });
});
