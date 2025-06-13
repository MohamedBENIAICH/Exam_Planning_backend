<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_du_local',
        'departement',
        'capacite',
        'liste_des_equipements',
        // 'disponible_pour_planification'
    ];

    protected $casts = [
        'liste_des_equipements' => 'array',
        // 'disponible_pour_planification' => 'boolean',
        'capacite' => 'integer'
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class);
    }
}