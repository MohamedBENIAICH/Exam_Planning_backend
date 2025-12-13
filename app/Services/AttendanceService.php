<?php

namespace App\Services;

use App\Repositories\AttendanceRepository;

class AttendanceService
{
    protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function recordAttendance(array $data)
    {
        return $this->attendanceRepository->updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'exam_id' => $data['exam_id'],
            ],
            [
                'status' => $data['status'],
                'attended_at' => now(),
            ]
        );
    }

    public function getAttendancesByExamId($examId)
    {
        return $this->attendanceRepository->getByExamId($examId);
    }

    public function getAttendanceByStudentAndExam($studentId, $examId)
    {
        return $this->attendanceRepository->findByStudentAndExam($studentId, $examId);
    }
}
