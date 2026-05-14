<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agriculteur extends Model
{
    use HasFactory;

    protected $table = 'agriculteur';
    public $incrementing = false; // Car l'ID vient de Utilisateur
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'typeParcelle',
        'nbParcelles',
        'nom'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id');
    }
}
