<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $table = 'commande';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'dateCommande',
        'status',
        'totalPrix',
        'adresseLivraison'
    ];

    protected $casts = [
        'dateCommande' => 'date',
        'totalPrix' => 'double'
    ];

    public function client()
    {
        return $this->belongsTo(Utilisateur::class, 'client_id');
    }

    public function produits()
    {
        return $this->hasMany(CommandeProduit::class, 'commande_id');
    }
}
