<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Champs assignables en masse
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'sexe',
        'photo',
        'password',
    ];

    // Champs cachés lors de la sérialisation (ex: json)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Cast automatique, password hashé à l'écriture (Laravel 10+)
    protected $casts = [
        'password' => 'hashed',
    ];
}
