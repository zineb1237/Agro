<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\CommandeProduit;
use App\Models\Agriculteur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VendeurController extends Controller
{
    /**
     * Récupère l'utilisateur connecté de manière sécurisée
     */
    private function getVendeur()
    {
        return auth('sanctum')->user();
    }

    /**
     * Statistiques pour le vendeur
     */
    public function stats(Request $request)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $vendeur_id = $vendeur->id;

        try {
            $total_ventes = DB::table('commande_produit')
                ->join('produit', 'commande_produit.produit_id', '=', 'produit.id')
                ->where('produit.fournisseur_id', $vendeur_id)
                ->sum(DB::raw('commande_produit.prixUnitaire * commande_produit.quantite'));

            $produits_actifs = Produit::where('fournisseur_id', $vendeur_id)
                ->where('stock', '>', 0)
                ->count();

            $commandes_livrees = DB::table('commande')
                ->join('commande_produit', 'commande.id', '=', 'commande_produit.commande_id')
                ->join('produit', 'commande_produit.produit_id', '=', 'produit.id')
                ->where('produit.fournisseur_id', $vendeur_id)
                ->where('commande.status', 'Livré')
                ->distinct('commande.id')
                ->count();

            $clients_uniques = DB::table('commande')
                ->join('commande_produit', 'commande.id', '=', 'commande_produit.commande_id')
                ->join('produit', 'commande_produit.produit_id', '=', 'produit.id')
                ->where('produit.fournisseur_id', $vendeur_id)
                ->distinct('commande.client_id')
                ->count();

            $top_produits = DB::table('commande_produit')
                ->join('produit', 'commande_produit.produit_id', '=', 'produit.id')
                ->select('produit.nom', 
                         DB::raw('SUM(commande_produit.quantite) as total_vendus'), 
                         DB::raw('SUM(commande_produit.prixUnitaire * commande_produit.quantite) as revenu'))
                ->where('produit.fournisseur_id', $vendeur_id)
                ->groupBy('produit.id', 'produit.nom')
                ->orderBy('total_vendus', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                "total_ventes" => (float)$total_ventes,
                "produits_actifs" => (int)$produits_actifs,
                "commandes_livrees" => (int)$commandes_livrees,
                "clients_uniques" => (int)$clients_uniques,
                "top_produits" => $top_produits
            ]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Liste des produits du vendeur
     */
    public function produits(Request $request)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $produits = Produit::where('fournisseur_id', $vendeur->id)->get();

        $formatted = $produits->map(function ($row) {
            $icon = '📦';
            $type = strtolower($row->type);
            if (strpos($type, 'semence') !== false) $icon = '🌱';
            elseif (strpos($type, 'engrais') !== false) $icon = '🍃';
            elseif (strpos($type, 'materiel') !== false) $icon = '🚜';
            elseif (strpos($type, 'pesticide') !== false) $icon = '🧪';

            return [
                "id" => (int)$row->id,
                "nom" => $row->nom,
                "type" => $row->type,
                "prix" => (float)$row->prixUnitaire,
                "stock" => (int)$row->stock,
                "images" => $row->images,
                "description" => $row->description,
                "statut" => $row->statut,
                "promotion" => (bool)$row->promotion,
                "certificationBio" => (bool)$row->certificationBio,
                "icon" => $icon
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Ajouter un produit
     */
    public function storeProduit(Request $request)
    {
        ini_set('memory_limit', '256M');

        try {
            $vendeur = $this->getVendeur();
            if (!$vendeur) {
                return response()->json(["success" => false, "error" => "Vendeur non identifié"], 401);
            }

            $data = $request->all();
            
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageData = base64_encode(file_get_contents($image->getRealPath()));
                    $mimeType = $image->getMimeType();
                    $images[] = 'data:' . $mimeType . ';base64,' . $imageData;
                }
            }

            $produit = new Produit();
            $produit->fournisseur_id = $vendeur->id;
            $produit->nom = $data['nom'] ?? 'Sans nom';
            $produit->type = $data['type'] ?? 'Autre';
            $produit->sous_categorie = $data['sous_categorie'] ?? null;
            $produit->prixUnitaire = (float)($data['prix'] ?? 0);
            $produit->stock = (int)($data['stock'] ?? 0);
            $produit->unite_vente = $data['unite_vente'] ?? 'kg';
            $produit->description = $data['description'] ?? '';
            $produit->statut = $data['statut'] ?? 'disponible';
            $produit->images = $images;
            $produit->promotion = filter_var($data['promotion'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $produit->certificationBio = filter_var($data['certificationBio'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $produit->dateAjout = now();
            
            $produit->save();

            return response()->json(["success" => true, "id" => $produit->id]);

        } catch (\Exception $e) {
            Log::error('Erreur storeProduit: ' . $e->getMessage());
            return response()->json(["success" => false, "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Modifier un produit
     */
    public function updateProduit(Request $request)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $data = $request->all();
        $produit = Produit::where('id', $data['id'])
                         ->where('fournisseur_id', $vendeur->id)
                         ->first();
                         
        if (!$produit) return response()->json(["success" => false, "error" => "Produit non autorisé"]);

        if ($request->hasFile('images')) {
            $images = $produit->images ?? [];
            foreach ($request->file('images') as $image) {
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $mimeType = $image->getMimeType();
                $images[] = 'data:' . $mimeType . ';base64,' . $imageData;
            }
            $produit->images = $images;
        } elseif ($request->hasFile('image')) {
            // Handle singular 'image' field from frontend
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();
            $produit->images = ['data:' . $mimeType . ';base64,' . $imageData];
        }

        $produit->nom = $data['nom'] ?? $produit->nom;
        $produit->type = $data['type'] ?? $produit->type;
        $produit->prixUnitaire = $data['prix'] ?? $produit->prixUnitaire;
        $produit->stock = $data['stock'] ?? $produit->stock;
        $produit->description = $data['description'] ?? $produit->description;
        $produit->statut = $data['statut'] ?? $produit->statut;
        $produit->promotion = filter_var($data['promotion'] ?? $produit->promotion, FILTER_VALIDATE_BOOLEAN);
        $produit->certificationBio = filter_var($data['certificationBio'] ?? $produit->certificationBio, FILTER_VALIDATE_BOOLEAN);
        $produit->save();

        return response()->json(["success" => true]);
    }

    /**
     * Supprimer un produit
     */
    public function destroyProduit($id)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $produit = Produit::where('id', $id)->where('fournisseur_id', $vendeur->id)->first();
        if ($produit) {
            $produit->delete();
            return response()->json(["success" => true]);
        }
        return response()->json(["success" => false, "error" => "Produit non trouvé"]);
    }

    /**
     * Liste des commandes reçues
     */
    public function commandes(Request $request)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $results = DB::table('commande as c')
            ->leftJoin('agriculteur as u', 'c.client_id', '=', 'u.id')
            ->join('commande_produit as cp', 'c.id', '=', 'cp.commande_id')
            ->join('produit as p', 'cp.produit_id', '=', 'p.id')
            ->select('c.id', 'c.client_id', 'c.dateCommande as date', 'c.status', 'c.adresseLivraison as adresse', 
                     'u.nom as agriculteur_nom', 
                     'cp.produit_id', 'cp.quantite', 'cp.prixUnitaire', 'p.nom as produit_nom')
            ->where('p.fournisseur_id', $vendeur->id)
            ->orderBy('c.dateCommande', 'desc')
            ->get();

        $commandes = [];
        foreach ($results as $row) {
            $id = $row->id;
            if (!isset($commandes[$id])) {
                $commandes[$id] = [
                    "id" => $id,
                    "date" => $row->date,
                    "status" => $row->status,
                    "adresse" => $row->adresse,
                    "agriculteur_nom" => $row->agriculteur_nom ?? 'Client #'.$row->client_id,
                    "produits" => [],
                    "total" => 0
                ];
            }
            $commandes[$id]["produits"][] = [
                "nom" => $row->produit_nom,
                "quantite" => (int)$row->quantite,
                "prixUnitaire" => (float)$row->prixUnitaire
            ];
            $commandes[$id]["total"] += ($row->quantite * $row->prixUnitaire);
        }

        return response()->json(array_values($commandes));
    }

    /**
     * Livrer une commande
     */
    public function livrer(Request $request)
    {
        $vendeur = $this->getVendeur();
        if (!$vendeur) return response()->json(["error" => "Non authentifié"], 401);

        $id = $request->input('commande_id');
        $commande = Commande::find($id);
        if ($commande) {
            $commande->status = 'Livré';
            $commande->save();
            return response()->json(["success" => true]);
        }
        return response()->json(["success" => false]);
    }
}
