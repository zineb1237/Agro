<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable, CanResetPassword;

    protected $table = 'utilisateur';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'email', 'motDePasse', 'role', 'telephone', 'adresse', 'ville',
        'provider', 'provider_id', 'avatar'
    ];

    protected $hidden = ['motDePasse'];

    public function getAuthPassword()
    {
        return $this->motDePasse;
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function getAuthPasswordName()
    {
        return 'motDePasse';
    }
}