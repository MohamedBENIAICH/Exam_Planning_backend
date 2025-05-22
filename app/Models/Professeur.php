<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'nom',
        'prenom',
        'departement'
    ];

    /**
     * The exams that the professor is assigned to.
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_professeur')
            ->withTimestamps();
    }
}
