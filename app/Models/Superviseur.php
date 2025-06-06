<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Superviseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'service',
        'nom',
        'prenom',
        'poste',
        'email'
    ];

    /**
     * The exams that the supervisor is assigned to.
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_superviseur')
            ->withTimestamps();
    }
}
