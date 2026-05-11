<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:utilisateur,email',
            'password'  => 'required|min:6|confirmed',
            'ville'     => 'nullable|string|max:255',      // facultatif
            'telephone' => 'nullable|string|max:20',       // facultatif
        ]);

        $user = Utilisateur::create([
            'nom'       => $request->name,
            'email'     => $request->email,
            'motDePasse' => Hash::make($request->password),
            'ville'     => $request->ville,                // nouveau
            'telephone' => $request->telephone,            // nouveau
        ]);

        // Création du token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->nom,
                'email' => $user->email,
                'ville' => $user->ville,
                'telephone' => $user->telephone,
            ],
            'token' => $token,
        ], 201);
    }

    // Connexion
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->motDePasse)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    // Optionnel : déconnexion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }
}