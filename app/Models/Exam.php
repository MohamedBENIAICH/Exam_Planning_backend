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
}
