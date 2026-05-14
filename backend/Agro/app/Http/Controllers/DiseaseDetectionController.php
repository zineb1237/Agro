<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiseaseDetectionController extends Controller
{
    /**
     * Analyse une image de plante en utilisant le modèle FastAPI.
     */
    public function detect(Request $request)
    {
        // 1. Validation de l'image
        $request->validate([
            'image' => 'required|image|max:5120', // Max 5MB
        ]);

        try {
            $image = $request->file('image');
            
            // 2. Transmission de l'image à FastAPI (Python)
            // L'image est envoyée en tant que multipart/form-data
            $response = Http::attach(
                'file', 
                file_get_contents($image->getRealPath()), 
                $image->getClientOriginalName()
            )->post('http://127.0.0.1:8001/predict');

            // 3. Gestion de la réponse de FastAPI
            if ($response->successful()) {
                $data = $response->json();
                
                // On peut enrichir la réponse ici avec des conseils de Laravel
                // ou simplement retourner le JSON de FastAPI
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la détection par le modèle IA.',
                'error' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Erreur Détection Maladie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors du traitement de l\'image.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
