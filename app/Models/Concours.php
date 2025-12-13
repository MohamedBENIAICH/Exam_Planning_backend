<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConcoursClassroomAssignment;

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
        'status',
        'supervisors_notified',
        'candidats_notified',
        'supervisors_notified_at',
        'candidats_notified_at'
    ];

    protected $casts = [
        'date_concours' => 'date',
        'locaux' => 'array',
        'supervisors_notified' => 'boolean',
        'candidats_notified' => 'boolean',
        'supervisors_notified_at' => 'datetime',
        'candidats_notified_at' => 'datetime'
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

    /**
     * Get the classroom assignments for the concours.
     */
    public function classroomAssignments()
    {
        return $this->hasMany(ConcoursClassroomAssignment::class);
    }
}
