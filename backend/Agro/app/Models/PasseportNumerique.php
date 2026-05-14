<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasseportNumerique extends Model
{
    use HasFactory;

    protected $table = 'passeport_numerique';
    public $timestamps = false;

    protected $fillable = [
        'parcelle_id',
        'utilisateur_id',
        'qrCode',
        'historiqueActions'
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class, 'parcelle_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
