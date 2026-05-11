<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\ParcelleController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\AgroBotController;

Route::post('/agrobot/ask', [AgroBotController::class, 'ask']);
Route::post('/agrobot/transcribe', [AgroBotController::class, 'transcribe']);
Route::post('/predict', [PredictionController::class, 'predict']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('calendar-events', CalendarEventController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('calendar-events/upcoming', [CalendarEventController::class, 'upcomingEvents']);
    Route::patch('calendar-events/{id}/mark-notified', [CalendarEventController::class, 'markNotified']);
    
    // Notifications
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/parcelles', [ParcelleController::class, 'index']);
    Route::post('/parcelles', [ParcelleController::class, 'store']);
    Route::get('/parcelles/{id}', [ParcelleController::class, 'show']);
    Route::put('/parcelles/{id}', [ParcelleController::class, 'update']);
    Route::delete('/parcelles/{id}', [ParcelleController::class, 'destroy']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);
