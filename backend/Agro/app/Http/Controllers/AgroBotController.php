<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AgroBotController extends Controller
{
    public function ask(Request $request)
    {
        $question = $request->input('question');
        $history = $request->input('history', '');

        $response = Http::timeout(30)->asForm()->post('http://localhost:8001/ask', [
            'question' => $question,
            'history' => $history,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Service indisponible'], 500);
        }

        return $response->json();
    }

    public function transcribe(Request $request)
    {
        $audioFile = $request->file('audio');
        if (!$audioFile) {
            return response()->json(['error' => 'Aucun fichier audio'], 400);
        }

        $response = Http::timeout(30)->attach(
            'file', file_get_contents($audioFile->getRealPath()), 'audio.wav'
        )->post('http://localhost:8001/transcribe');

        if ($response->failed()) {
            return response()->json(['error' => 'Transcription échouée'], 500);
        }

        return $response->json();
    }
}