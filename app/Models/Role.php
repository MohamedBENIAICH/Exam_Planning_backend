<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Relation : Un rôle peut avoir plusieurs utilisateurs.
     */
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class);
    }
}
