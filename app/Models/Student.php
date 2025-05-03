<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'numero_etudiant',
        'email',
        'filiere',
        'niveau',
        'qr_code',
        'cne'
    ];

    protected $rules = [
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'cne' => 'required|string|max:255',
        'email' => 'required|email|unique:students,email',
        'filiere' => 'required|string|max:255',
        'niveau' => 'required|string|max:255'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the exams for the student.
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class);
    }
}
