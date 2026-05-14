<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $table = 'produit';
    public $timestamps = false;

    protected $fillable = [
        'fournisseur_id',
        'nom',
        'type',
        'sous_categorie',
        'prixUnitaire',
        'stock',
        'unite_vente',
        'description',
        'images',
        'dateAjout',
        'promotion',
        'certificationBio',
        'statut'
    ];

    protected $casts = [
        'dateAjout' => 'datetime',
        'prixUnitaire' => 'double',
        'stock' => 'integer',
        'promotion' => 'boolean',
        'certificationBio' => 'boolean',
        'images' => 'array'
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Utilisateur::class, 'fournisseur_id');
    }
}
