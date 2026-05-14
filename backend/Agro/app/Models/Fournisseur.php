<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;

    protected $table = 'fournisseur';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'typeProduits',
        'rating'
    ];

    protected $casts = [
        'rating' => 'double'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id');
    }
}
