<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle',
        'filiere',
        'module',
        'date_examen',
        'heure_debut',
        'heure_fin',
        'locaux',
        'superviseurs'
    ];

    protected $casts = [
        'date_examen' => 'date',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class);
    }

    /**
     * The supervisors assigned to the exam.
     */
    public function superviseurs()
    {
        return $this->belongsToMany(Superviseur::class, 'exam_superviseur')
            ->withTimestamps();
    }
}
