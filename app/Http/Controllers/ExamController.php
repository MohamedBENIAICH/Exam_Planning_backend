<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Fetch exams with their related students
            $exams = Exam::with('students')->get();

            return response()->json([
                'status' => 'success',
                'data' => $exams
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam with its students.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $exam = Exam::with('students')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $exam
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the total number of exams in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        try {
            $count = Exam::count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'cycle' => 'required|string',
            'filiere' => 'required|string',
            'module' => 'required|string',
            'date_examen' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i',
            'locaux' => 'required|string',
            'superviseurs' => 'required|string',
            'students' => 'required|array',
            'students.*.studentId' => 'required|string',
            'students.*.firstName' => 'required|string',
            'students.*.lastName' => 'required|string',
            'students.*.email' => 'required|email',
            'students.*.program' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Create the exam
            $exam = Exam::create([
                'cycle' => $request->cycle,
                'filiere' => $request->filiere,
                'module' => $request->module,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'locaux' => $request->locaux,
                'superviseurs' => $request->superviseurs
            ]);

            // Process students
            $studentIds = [];
            foreach ($request->students as $studentData) {
                // Check if student already exists
                $student = Student::where('numero_etudiant', $studentData['studentId'])->first();

                if (!$student) {
                    // Create new student if not exists
                    $student = Student::create([
                        'nom' => $studentData['lastName'],
                        'prenom' => $studentData['firstName'],
                        'numero_etudiant' => $studentData['studentId'],
                        'email' => $studentData['email'],
                        'filiere' => $studentData['program'],
                        'niveau' => 'L3' // Default value, adjust as needed
                    ]);
                }

                $studentIds[] = $student->id;
            }

            // Attach students to the exam
            $exam->students()->attach($studentIds);

            // Commit the transaction
            DB::commit();

            // Load the students relationship
            $exam->load('students');

            return response()->json([
                'status' => 'success',
                'message' => 'Exam created successfully',
                'data' => $exam
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $exam = Exam::findOrFail($id);

            // Delete the exam
            $exam->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Exam deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the last 5 exams created.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestExams()
    {
        try {
            $latestExams = Exam::with('students')
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $latestExams
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve latest exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
