<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConcoursClassroomAssignment;

class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'CNE',
        'CIN',
        'nom',
        'prenom',
        'email',
    ];

    public function concours()
    {
        return $this->belongsToMany(Concours::class, 'concours_candidat');
    }

    /**
     * Get the classroom assignments for the candidat.
     */
    public function classroomAssignments()
    {
        return $this->hasMany(ConcoursClassroomAssignment::class);
    }
}
