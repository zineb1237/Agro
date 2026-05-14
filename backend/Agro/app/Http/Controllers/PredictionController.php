<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        $validated = $request->validate([
            'culture' => 'required|string',
            'variete' => 'required|string',
            'region' => 'required|string',
            'type_sol' => 'required|string',
            'irrigation' => 'required|boolean',
            'n_kg_ha' => 'required|numeric',
            'p2o5_kg_ha' => 'required|numeric',
            'k2o_kg_ha' => 'required|numeric',
            'semis_jour' => 'required|integer|min:1|max:365',
        ]);

        // Appel à l'API Python sur le port 8001
        $response = Http::post('http://localhost:8001/predict', $validated);

        if ($response->failed()) {
            return response()->json(['error' => 'Service de prédiction indisponible'], 500);
        }

        return $response->json();
    }
}