<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Students",
 *     description="API Endpoints for managing students"
 * )
 */
class StudentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/students",
     *     summary="Get all students",
     *     tags={"Students"},
     *     @OA\Response(
     *         response=200,
     *         description="List of students",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="prenom", type="string"),
     *                 @OA\Property(property="numero_etudiant", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="filiere", type="string"),
     *                 @OA\Property(property="niveau", type="string")
     *             )
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/students/count",
     *     summary="Get total number of students",
     *     tags={"Students"},
     *     @OA\Response(
     *         response=200,
     *         description="Total number of students",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="count", type="integer")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/students/by-exam/{examId}",
     *     summary="Get students by exam ID",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="examId",
     *         in="path",
     *         required=true,
     *         description="Exam ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of students for the exam",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="prenom", type="string"),
     *                 @OA\Property(property="numero_etudiant", type="string"),
     *                 @OA\Property(property="email", type="string")
     *             )
     *         )
     *     )
     * )
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
