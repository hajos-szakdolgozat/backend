<?php

use App\Http\Controllers\BoatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});






Route::middleware(['auth:sanctum'])->group(function () {

    // felhasználók
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    //kedvencek
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('{boatId}', [FavoriteController::class, 'store']);
        Route::delete('{boatId}', [FavoriteController::class, 'destroy']);
    });

    // hajók
    Route::prefix('boats')->group(function () {
        Route::get('/', [BoatController::class, 'index']);
        Route::get('{boat}', [BoatController::class, 'show']);
        Route::post('/', [BoatController::class, 'store']);
        Route::put('{boat}', [BoatController::class, 'update']);
        Route::delete('{boat}', [BoatController::class, 'destroy']);
    });

    // foglalások
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::get('/mine', [ReservationController::class, 'myReservations']);
        Route::get('/mine/{id}', [ReservationController::class, 'myReservation']);
        Route::post('/', [ReservationController::class, 'store']);
    });
});
