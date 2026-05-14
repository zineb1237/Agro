<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'prenom' => explode(' ', $user->nom)[0] ?? '',
            'nom' => count(explode(' ', $user->nom)) > 1 ? implode(' ', array_slice(explode(' ', $user->nom), 1)) : $user->nom,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'adresse' => $user->adresse,
            'ville' => $user->ville,
            'role' => $user->role ?? 'agriculteur',
            'photo' => $user->avatar
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $user->nom = trim(($request->prenom ?? '') . ' ' . ($request->nom ?? ''));
        if (empty($user->nom)) {
             $user->nom = $user->getOriginal('nom'); // fallback
        }
        
        $user->telephone = $request->telephone ?? $user->telephone;
        $user->adresse = $request->adresse ?? $user->adresse;
        $user->ville = $request->ville ?? $user->ville;
        
        if ($request->has('role')) {
             $user->role = $request->role;
        }

        $user->save();

        return response()->json(['success' => true]);
    }

    public function uploadPhoto(Request $request)
    {
        $user = $request->user();

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();
            $base64Image = 'data:' . $mimeType . ';base64,' . $imageData;

            $user->avatar = $base64Image;
            $user->save();

            return response()->json(['success' => true, 'photo_url' => $base64Image]);
        }

        return response()->json(['success' => false, 'error' => 'Aucune image trouvée']);
    }
}
