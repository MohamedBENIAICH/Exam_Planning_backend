<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\BaseRepository;

class StudentRepository extends BaseRepository
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function findByNumeroEtudiant($numeroEtudiant)
    {
        return $this->model->where('numero_etudiant', $numeroEtudiant)->first();
    }

    public function getByFiliere($filiere)
    {
        return $this->model->where('filiere', $filiere)->get();
    }

    public function getByExamId($examId)
    {
        return $this->model->whereHas('exams', function ($query) use ($examId) {
            $query->where('exam_id', $examId);
        })->get();
    }
}
