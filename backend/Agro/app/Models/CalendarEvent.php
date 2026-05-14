<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'proprietaire_id', 'parcelle_id', 'title', 'start_date', 'end_date',
        'type', 'description', 'background_color', 'notified_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'notified_at' => 'datetime',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(Utilisateur::class, 'proprietaire_id');
    }

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }
}