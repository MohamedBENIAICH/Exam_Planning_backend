<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation',
        'filiere',
        'module_id',
        'semestre',
        'date_examen',
        'heure_debut',
        'heure_fin',
        'locaux',
        'superviseurs',
        'professeurs',
        'status'
    ];

    protected $casts = [
        'date_examen' => 'date',
        'heure_debut' => 'datetime:H:i',
        'heure_fin' => 'datetime:H:i',
        'formation' => 'integer',
        'filiere' => 'integer',
        'module_id' => 'integer'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation', 'id_formation');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere', 'id_filiere');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id', 'id_module');
    }

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

    /**
     * The professors assigned to the exam.
     */
    public function professeurs()
    {
        return $this->belongsToMany(Professeur::class, 'exam_professeur')
            ->withTimestamps();
    }
}
