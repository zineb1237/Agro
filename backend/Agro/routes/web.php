<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialController::class, 'callback']);

// Toutes les autres routes → vue existante 'LandingPage'
Route::view('/{any?}', 'LandingPage')->where('any', '.*');

// Route pour la détection des maladies (Relais vers FastAPI)
Route::post('/api/detect-disease', [DiseaseDetectionController::class, 'detect']);
