<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiseaseDetectionController;

Route::get('/', function () {
    return view('welcome');
});

// Route pour la détection des maladies (Relais vers FastAPI)
Route::post('/api/detect-disease', [DiseaseDetectionController::class, 'detect']);
