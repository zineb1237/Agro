<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Utilisateur;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Lien de réinitialisation envoyé !'], 200);
        }

        // Traduction des erreurs Laravel
        $message = match ($status) {
            Password::RESET_THROTTLED => 'Veuillez patienter avant de réessayer.',
            Password::INVALID_USER => 'Aucun utilisateur trouvé avec cette adresse email.',
            default => 'Impossible d\'envoyer le lien de réinitialisation.'
        };

        return response()->json(['message' => $message], 400);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Utilisateur $user, string $password) {
                $user->motDePasse = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mot de passe réinitialisé avec succès'], 200)
            : response()->json(['message' => 'Token invalide ou email incorrect'], 400);
    }
}