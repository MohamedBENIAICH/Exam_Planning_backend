<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concours extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'date_concours',
        'heure_debut',
        'heure_fin',
        'locaux',
        'type_epreuve',
        'status'
    ];

    public function candidats()
    {
        return $this->belongsToMany(Candidat::class, 'concours_candidat');
    }

    public function superviseurs()
    {
        return $this->belongsToMany(Superviseur::class, 'concours_superviseur');
    }

    public function professeurs()
    {
        return $this->belongsToMany(Professeur::class, 'concours_professeur');
    }
}
