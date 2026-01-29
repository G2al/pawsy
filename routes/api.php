<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PetController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/services', function () {
    $services = \App\Models\Service::where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function ($service) {
            if ($service->photo) {
                $service->photo_url = asset('storage/' . $service->photo);
            } else {
                $service->photo_url = null;
            }
            return $service;
        });
    
    return response()->json([
        'success' => true,
        'services' => $services
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    Route::get('/my-pets', [BookingController::class, 'myPets']);
    Route::apiResource('pets', PetController::class)->only(['store', 'update', 'destroy']);
    Route::get('/available-slots', [BookingController::class, 'availableSlots']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
});
