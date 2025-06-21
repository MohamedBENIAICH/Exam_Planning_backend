<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Enregistrement de la présence (appelé par l'app mobile)
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'exam_id' => 'required|exists:exams,id',
            'status' => 'required|in:present,absent',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'exam_id' => $data['exam_id'],
            ],
            [
                'status' => $data['status'],
                'attended_at' => now(),
            ]
        );

        return response()->json($attendance, 201);
    }

    // Récupération des présences pour un examen (pour le web avant PDF)
    public function index(Request $request)
    {
        $examId = $request->query('exam_id');
        $attendances = Attendance::with('student')
            ->where('exam_id', $examId)
            ->get();

        return response()->json($attendances);
    }
}
