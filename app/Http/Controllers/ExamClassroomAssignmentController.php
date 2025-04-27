<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\ExamClassroomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamClassroomAssignmentController extends Controller
{
    /**
     * Create or update assignments for an exam.
     */
    public function store(Request $request, $exam_id)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'classroom_ids' => 'required|array',
                'classroom_ids.*' => 'exists:classrooms,id',
                'student_numeros' => 'required|array',
                'student_numeros.*' => 'exists:students,numero_etudiant'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get exam and validate it exists
            $exam = Exam::findOrFail($exam_id);

            // Get classrooms and calculate total capacity
            $classrooms = Classroom::whereIn('id', $request->classroom_ids)->get();
            $totalCapacity = $classrooms->sum('capacite');

            // Check if we have enough capacity
            if ($totalCapacity < count($request->student_numeros)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient classroom capacity',
                    'details' => [
                        'total_students' => count($request->student_numeros),
                        'total_capacity' => $totalCapacity
                    ]
                ], 400);
            }

            // Resolve student_numeros to student_ids (preserving order)
            $students = Student::whereIn('numero_etudiant', $request->student_numeros)->get()->keyBy('numero_etudiant');
            $studentIds = [];
            foreach ($request->student_numeros as $numero) {
                if (isset($students[$numero])) {
                    $studentIds[] = $students[$numero]->id;
                }
            }

            // Start transaction
            DB::beginTransaction();

            // Delete existing assignments for this exam
            ExamClassroomAssignment::where('exam_id', $exam_id)->delete();

            // Assign students to classrooms
            $assignments = [];
            $currentClassroomIndex = 0;
            $currentSeatNumber = 1;

            foreach ($studentIds as $studentId) {
                $classroom = $classrooms[$currentClassroomIndex];

                // Create assignment
                $assignment = ExamClassroomAssignment::create([
                    'exam_id' => $exam_id,
                    'classroom_id' => $classroom->id,
                    'student_id' => $studentId,
                    'seat_number' => $currentSeatNumber
                ]);

                $assignments[] = $assignment;

                // Move to next seat
                $currentSeatNumber++;

                // If classroom is full, move to next classroom
                if ($currentSeatNumber > $classroom->capacite) {
                    $currentClassroomIndex++;
                    $currentSeatNumber = 1;
                }
            }

            DB::commit();

            // Prepare response
            $response = [
                'status' => 'success',
                'data' => [
                    'exam_id' => $exam_id,
                    'assignments' => []
                ]
            ];

            // Group assignments by classroom
            foreach ($classrooms as $classroom) {
                $classroomAssignments = ExamClassroomAssignment::with('student')
                    ->where('exam_id', $exam_id)
                    ->where('classroom_id', $classroom->id)
                    ->get();

                $response['data']['assignments'][] = [
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                    'capacity' => $classroom->capacite,
                    'students' => $classroomAssignments->map(function ($assignment) {
                        return [
                            'student_id' => $assignment->student->id,
                            'first_name' => $assignment->student->first_name ?? $assignment->student->prenom ?? '',
                            'last_name' => $assignment->student->last_name ?? $assignment->student->nom ?? '',
                            'seat_number' => $assignment->seat_number
                        ];
                    })->values()
                ];
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignments for an exam.
     */
    public function show($exam_id)
    {
        try {
            $exam = Exam::findOrFail($exam_id);

            $assignments = ExamClassroomAssignment::with(['classroom', 'student'])
                ->where('exam_id', $exam_id)
                ->get()
                ->groupBy('classroom_id');

            $response = [
                'status' => 'success',
                'data' => [
                    'exam_id' => $exam_id,
                    'assignments' => []
                ]
            ];

            foreach ($assignments as $classroomId => $classroomAssignments) {
                $classroom = $classroomAssignments->first()->classroom;

                $response['data']['assignments'][] = [
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->nom_du_local,
                    'capacity' => $classroom->capacite,
                    'students' => $classroomAssignments->map(function ($assignment) {
                        return [
                            'student_id' => $assignment->student->id,
                            'first_name' => $assignment->student->prenom,
                            'last_name' => $assignment->student->nom,
                            'seat_number' => $assignment->seat_number
                        ];
                    })
                ];
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all assignments for an exam.
     */
    public function destroy($exam_id)
    {
        try {
            $exam = Exam::findOrFail($exam_id);

            ExamClassroomAssignment::where('exam_id', $exam_id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Assignments deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
