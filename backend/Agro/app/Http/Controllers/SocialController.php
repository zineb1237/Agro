<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Les providers autorisés
     */
    private array $providers = ['google', 'facebook'];

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect(env('FRONTEND_URL', 'http://localhost:8080') . '/login?error=' . urlencode('Provider non supporté'));
        }

        // stateless() évite les problèmes de session/state entre backend et frontend séparés
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(Request $request, $provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect(env('FRONTEND_URL', 'http://localhost:8080') . '/login?error=' . urlencode('Provider non supporté'));
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');

        // Vérifier si Facebook a renvoyé une erreur
        if ($request->has('error')) {
            Log::error('Social login denied by user: ' . $request->get('error_description', $request->get('error')));
            return redirect($frontendUrl . '/login?error=' . urlencode($request->get('error_description', 'Connexion refusée')));
        }

        // Vérifier que le code d'autorisation est présent
        if (!$request->has('code')) {
            Log::error("Social login error: Missing 'code' parameter in callback for {$provider}. Query: " . json_encode($request->query()));
            return redirect($frontendUrl . '/login?error=' . urlencode('Code d\'autorisation manquant. Réessayez.'));
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            Log::error("Social login error ({$provider}): " . $e->getMessage());
            return redirect($frontendUrl . '/login?error=' . urlencode('Erreur de connexion sociale: ' . $e->getMessage()));
        }

        // Vérifier que l'email est disponible
        $email = $socialUser->getEmail();
        if (!$email) {
            Log::error("Social login error: No email returned from {$provider}");
            return redirect($frontendUrl . '/login?error=' . urlencode('Aucun email fourni par ' . $provider . '. Vérifiez les permissions de votre compte.'));
        }

        // Trouver ou créer l'utilisateur
        $user = Utilisateur::where('email', $email)->first();

        if (!$user) {
            $user = Utilisateur::create([
                'nom'         => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                'email'       => $email,
                'motDePasse'  => bcrypt(Str::random(16)),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar'      => $socialUser->getAvatar(),
            ]);
            Log::info("New user created via {$provider}: {$email}");
        } else {
            $user->update([
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar'      => $socialUser->getAvatar(),
            ]);
            Log::info("Existing user logged in via {$provider}: {$email}");
        }

        // Créer un token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Construire les données utilisateur
        $userData = json_encode([
            'id'    => $user->id,
            'name'  => $user->nom,
            'email' => $user->email,
        ]);

        // Rediriger vers le frontend Vue.js (DashboardPage)
        $redirectUrl = $frontendUrl . '/dashboard?token=' . urlencode($token) . '&user=' . urlencode($userData);

        Log::info("Social login successful, redirecting to dashboard for: {$email}");
        return redirect($redirectUrl);
    }
}