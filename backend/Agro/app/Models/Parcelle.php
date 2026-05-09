<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Utilisateur;
class Parcelle extends Model
{
    use HasFactory;

    // Nom de la table (singulier car vous avez utilisé "parcelle" dans la migration)
    protected $table = 'parcelle';

    // Champs pouvant être remplis en masse
    protected $fillable = [
        'proprietaire_id',
        'nom',
        'superficie',
        'localisation',
        'typeSol',
        'culture',
        'stade',
        'datePlantation',
        'statut'
    ];

    // Casts automatiques
    protected $casts = [
        'datePlantation' => 'date',
        'superficie' => 'decimal:2',
    ];

    // Relation inverse : une parcelle appartient à un utilisateur
    public function proprietaire()
    {
        return $this->belongsTo(Utilisateur::class, 'proprietaire_id');
    }
}