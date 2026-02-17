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


//apiResource létrehozza a route-okat (GET, POST stb)
Route::apiResource('transactions', TransactionController::class);
Route::apiResource('ports', PortController::class);
Route::apiResource('amenities', AmenityController::class);

// Pivot table – csak index, store, destroy
Route::get('boat-amenities', [BoatAmenityController::class, 'index']);
Route::post('boat-amenities', [BoatAmenityController::class, 'store']);
Route::delete('boat-amenities/{boatAmenity}', [BoatAmenityController::class, 'destroy']);


Route::middleware(['auth:sanctum'])->group(function () {
    //értékelés létrehozása
    Route::post('reviews', [ReviewController::class, 'store']);

    // felhasználók
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
        //értékelések
        Route::get('{id}/reviews', [ReviewController::class, 'userReviews']);
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
        //értékelések
        Route::get('{id}/reviews', [ReviewController::class, 'boatReviews']);
        //képek
        Route::post('/', [BoatImageController::class, 'store']);
        Route::delete('/{imageId}', [BoatImageController::class, 'destroy']);
    });

    // foglalások
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::get('/mine', [ReservationController::class, 'myReservations']);
        Route::get('/mine/{id}', [ReservationController::class, 'myReservation']);
        Route::post('/', [ReservationController::class, 'store']);
    });
});
