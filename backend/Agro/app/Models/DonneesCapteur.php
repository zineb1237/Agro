<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonneesCapteur extends Model
{
    use HasFactory;

    protected $table = 'donnees_capteur';
    public $timestamps = false;

    protected $fillable = [
        'parcelle_id',
        'humiditeSol',
        'pH',
        'azote',
        'phosphore',
        'potassium',
        'eauIrriguee',
        'dateMesure'
    ];

    protected $casts = [
        'dateMesure' => 'date',
        'humiditeSol' => 'double',
        'pH' => 'double',
        'azote' => 'double',
        'phosphore' => 'double',
        'potassium' => 'double',
        'eauIrriguee' => 'double'
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class, 'parcelle_id');
    }
}
