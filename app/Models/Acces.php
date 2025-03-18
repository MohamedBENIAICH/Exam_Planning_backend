<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acces extends Model
{
    use HasFactory;

    // Définir la table associée au modèle (si elle ne suit pas la convention de nommage Laravel)
    protected $table = 'acces'; // Si la table s'appelle 'acces'

    // Définir les attributs mass assignables
    protected $fillable = [
        'role_id', 
        'composant_id', 
        'type_permission_id'
    ];

    /**
     * Définir la relation avec le modèle Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Définir la relation avec le modèle Composant
     */
    public function composant()
    {
        return $this->belongsTo(Composant::class);
    }

    /**
     * Définir la relation avec le modèle TypePermission
     */
    public function typePermission()
    {
        return $this->belongsTo(TypePermission::class);
    }
}
