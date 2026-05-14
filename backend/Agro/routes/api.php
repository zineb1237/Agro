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
    
    // User Profile Routes
    Route::get('/user/profile', [\App\Http\Controllers\UserController::class, 'getProfile']);
    Route::put('/user/profile', [\App\Http\Controllers\UserController::class, 'updateProfile']);
    Route::post('/user/photo', [\App\Http\Controllers\UserController::class, 'uploadPhoto']);
    Route::delete('/user/photo', function (Request $request) {
        $user = $request->user();
        $user->avatar = null;
        $user->save();
        return response()->json(['success' => true]);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Marché
Route::get('/produits', [\App\Http\Controllers\MarketController::class, 'index']);
Route::post('/commander', [\App\Http\Controllers\MarketController::class, 'commander']);

// Vendeur API
Route::middleware('auth:sanctum')->prefix('vendeur')->group(function () {
    Route::get('/stats', [\App\Http\Controllers\VendeurController::class, 'stats']);
    Route::get('/produits', [\App\Http\Controllers\VendeurController::class, 'produits']);
    Route::post('/produits', [\App\Http\Controllers\VendeurController::class, 'storeProduit']);
    Route::post('/produits/edit', [\App\Http\Controllers\VendeurController::class, 'updateProduit']);
    Route::delete('/produits/{id}', [\App\Http\Controllers\VendeurController::class, 'destroyProduit']);
    Route::get('/commandes', [\App\Http\Controllers\VendeurController::class, 'commandes']);
    Route::post('/commandes/livrer', [\App\Http\Controllers\VendeurController::class, 'livrer']);
});
