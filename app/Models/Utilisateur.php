<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    use HasFactory;

    protected $table = 'utilisateurs'; // Spécifie le nom de la table

    protected $fillable = [
        'nom_utilisateur',
        'email',
        'mot_de_passe',
        'role_id',
    ];

    /**
     * Relation : Un utilisateur appartient à un rôle.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}