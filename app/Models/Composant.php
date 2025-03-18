<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Composant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'url',
    ];

    // Définir les relations
    public function acces()
    {
        return $this->hasMany(Acces::class);
    }
}
