<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $students = Student::all();

            return response()->json([
                'status' => 'success',
                'data' => $students
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the total number of students in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        try {
            $count = Student::count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students by exam ID.
     *
     * @param  int  $examId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsByExamId($examId)
    {
        try {
            // Check if the exam exists
            $exam = Exam::findOrFail($examId);

            // Get students associated with this exam
            $students = $exam->students;

            return response()->json([
                'status' => 'success',
                'data' => $students
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve students for this exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
