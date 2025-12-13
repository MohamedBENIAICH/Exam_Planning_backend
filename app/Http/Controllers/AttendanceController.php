<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    // Enregistrement de la présence (appelé par l'app mobile)
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'exam_id' => 'required|exists:exams,id',
            'status' => 'required|in:present,absent',
        ]);

        try {
            $attendance = $this->attendanceService->recordAttendance($data);
            return response()->json($attendance, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Récupération des présences pour un examen (pour le web avant PDF)
    public function index(Request $request)
    {
        try {
            $examId = $request->query('exam_id');
            $attendances = $this->attendanceService->getAttendancesByExamId($examId);
            return response()->json($attendances);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve attendances',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
