<?php

use App\Http\Controllers\AmenityController;
use App\Http\Controllers\BoatAmenityController;
use App\Http\Controllers\BoatController;
use App\Http\Controllers\BoatImageController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('boats', [BoatController::class, 'index']);
Route::get('boats/{boat}', [BoatController::class, 'show']);
Route::get('boats/{id}/reviews', [ReviewController::class, 'boatReviews']);

Route::get('ports', [PortController::class, 'index']);
Route::get('ports/{port}', [PortController::class, 'show']);

Route::get('amenities', [AmenityController::class, 'index']);
Route::get('amenities/{amenity}', [AmenityController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    //értékelés létrehozása
    Route::post('reviews', [ReviewController::class, 'store']);

    //kedvencek
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('{boatId}', [FavoriteController::class, 'store']);
        Route::delete('{boatId}', [FavoriteController::class, 'destroy']);
    });

    // foglalások
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::get('/mine', [ReservationController::class, 'myReservations']);
        Route::get('/mine/{id}', [ReservationController::class, 'myReservation']);
        Route::post('/', [ReservationController::class, 'store']);
    });

    Route::middleware(['role:admin'])->group(function () {
        // felhasználók
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('{id}', [UserController::class, 'show']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::delete('{id}', [UserController::class, 'destroy']);
            //értékelések
            Route::get('{id}/reviews', [ReviewController::class, 'userReviews']);
        });

        // admin műveletek
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('ports', PortController::class)->except(['index', 'show']);
        Route::apiResource('amenities', AmenityController::class)->except(['index', 'show']);

        Route::post('boats', [BoatController::class, 'store']);
        Route::put('boats/{boat}', [BoatController::class, 'update']);
        Route::delete('boats/{boat}', [BoatController::class, 'destroy']);

        // Pivot table – csak admin módosíthat
        Route::get('boat-amenities', [BoatAmenityController::class, 'index']);
        Route::post('boat-amenities', [BoatAmenityController::class, 'store']);
        Route::delete('boat-amenities/{boatAmenity}', [BoatAmenityController::class, 'destroy']);

        // képek kezelése
        Route::post('boat-images', [BoatImageController::class, 'store']);
        Route::delete('boat-images/{imageId}', [BoatImageController::class, 'destroy']);
    });
});
