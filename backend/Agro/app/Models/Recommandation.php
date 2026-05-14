<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommandation extends Model
{
    use HasFactory;

    protected $table = 'recommandation';
    public $timestamps = false;

    protected $fillable = [
        'parcelle_id',
        'dateEmission',
        'typeAction',
        'quantite',
        'remarque'
    ];

    protected $casts = [
        'dateEmission' => 'datetime',
        'quantite' => 'double'
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class, 'parcelle_id');
    }
}
