<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConcoursClassroomAssignment;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_du_local',
        'departement',
        'capacite',
        'liste_des_equipements'
    ];

    protected $casts = [
        'liste_des_equipements' => 'array',
        'capacite' => 'integer'
    ];
    
    // Alias for capacite to maintain backward compatibility
    public function getCapacityAttribute()
    {
        return $this->capacite;
    }
    
    public function setCapacityAttribute($value)
    {
        $this->attributes['capacite'] = $value;
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class);
    }

    /**
     * Get the concours assignments for the classroom.
     */
    public function concoursAssignments()
    {
        return $this->hasMany(ConcoursClassroomAssignment::class);
    }
}