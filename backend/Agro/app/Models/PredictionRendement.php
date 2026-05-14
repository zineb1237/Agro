<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionRendement extends Model
{
    use HasFactory;

    protected $table = 'prediction_rendement';
    public $timestamps = false;

    protected $fillable = [
        'parcelle_id',
        'datePrediction',
        'rendementPrevu',
        'niveauConfiance'
    ];

    protected $casts = [
        'datePrediction' => 'date',
        'rendementPrevu' => 'double',
        'niveauConfiance' => 'double'
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class, 'parcelle_id');
    }
}
