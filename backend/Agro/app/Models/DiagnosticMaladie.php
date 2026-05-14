<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticMaladie extends Model
{
    use HasFactory;

    protected $table = 'diagnostic_maladie';
    public $timestamps = false;

    protected $fillable = [
        'parcelle_id',
        'maladie',
        'niveauGravite',
        'recommandation',
        'dateDiagnostic',
        'niveauConfiance'
    ];

    protected $casts = [
        'dateDiagnostic' => 'date',
        'niveauConfiance' => 'double'
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class, 'parcelle_id');
    }
}
