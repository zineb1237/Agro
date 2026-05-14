<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\CommandeProduit;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    /**
     * Liste tous les produits pour le marché
     */
    public function index()
    {
        $produits = Produit::all();
        
        $formatted = $produits->map(function ($row) {
            $icon = '📦';
            $type = strtolower($row->type);
            if (strpos($type, 'semence') !== false) $icon = '🌱';
            elseif (strpos($type, 'engrais') !== false) $icon = '🍃';
            elseif (strpos($type, 'materiel') !== false) $icon = '🚜';
            elseif (strpos($type, 'pesticide') !== false) $icon = '🧪';

            return [
                "id" => (int)$row->id,
                "fournisseur_id" => (int)$row->fournisseur_id,
                "nom" => $row->nom,
                "type" => $row->type,
                "prix" => (float)$row->prixUnitaire,
                "stock" => (int)$row->stock,
                "description" => $row->description,
                "promotion" => (bool)$row->promotion,
                "certificationBio" => (bool)$row->certificationBio,
                "images" => $row->images,
                "statut" => $row->statut,
                "icon" => $icon
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Passer une commande
     */
    public function commander(Request $request)
    {
        $data = $request->all();
        
        if (empty($data['cart'])) {
            return response()->json(["error" => "Données invalides"], 400);
        }

        return DB::transaction(function () use ($data) {
            $client_id = 1; // Simulé
            $total = 0;
            foreach ($data['cart'] as $item) {
                $total += ($item['prix'] * $item['quantity']);
            }

            $commande = Commande::create([
                'client_id' => $client_id,
                'dateCommande' => now(),
                'status' => 'En attente',
                'totalPrix' => $total,
                'adresseLivraison' => $data['adresse'] ?? ''
            ]);

            foreach ($data['cart'] as $item) {
                CommandeProduit::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item['id'],
                    'quantite' => $item['quantity'],
                    'prixUnitaire' => $item['prix']
                ]);

                // Déduire du stock
                $produit = Produit::find($item['id']);
                if ($produit) {
                    $produit->stock -= $item['quantity'];
                    $produit->save();
                }
            }

            return response()->json(["success" => true, "commande_id" => $commande->id]);
        });
    }
}
