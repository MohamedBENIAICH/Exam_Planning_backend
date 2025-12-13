<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Repositories\BaseRepository;

class AttendanceRepository extends BaseRepository
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function getByExamId($examId)
    {
        return $this->model->with('student')
            ->where('exam_id', $examId)
            ->get();
    }

    public function findByStudentAndExam($studentId, $examId)
    {
        return $this->model->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->first();
    }
}
