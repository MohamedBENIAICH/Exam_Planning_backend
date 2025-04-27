<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Formation;
use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'formation' => 'required|string|max:255',
                'filiere' => 'required|string|max:255',
                'module' => 'required|string|max:255',
                'semestre' => 'required|string|max:255',
                'date_examen' => 'required|date',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i',
                'locaux' => 'required|string|max:255',
                'superviseurs' => 'required|string|max:255',
                'classroom_ids' => 'nullable|array',
                'classroom_ids.*' => 'exists:classrooms,id',
                'students' => 'array',
                'students.*.studentId' => 'string',
                'students.*.firstName' => 'string',
                'students.*.lastName' => 'string',
                'students.*.email' => 'email',
                'students.*.program' => 'string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Start a database transaction
            DB::beginTransaction();

            // Create the exam
            $exam = Exam::create([
                'formation' => $request->formation,
                'filiere' => $request->filiere,
                'module' => $request->module,
                'semestre' => $request->semestre,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'locaux' => $request->locaux,
                'superviseurs' => $request->superviseurs
            ]);

            // Sync classrooms if provided
            if ($request->has('classroom_ids')) {
                // Ensure all classroom IDs are integers
                $classroomIds = is_array($request->classroom_ids) ? array_map('intval', $request->classroom_ids) : [];

                // Sync the classrooms with the exam
                $exam->classrooms()->sync($classroomIds);

                // Log the sync for debugging
                DB::table('logs')->insert([
                    'message' => "Synced classrooms " . implode(', ', $classroomIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Process students
            if ($request->has('students') && is_array($request->students)) {
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

                // Sync students
                $exam->students()->sync($studentIds);

                // Log the sync for debugging
                DB::table('logs')->insert([
                    'message' => "Synced students " . implode(', ', $studentIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Load the exam with its relationships
            $exam->load(['students']);

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

    /**
     * Update the specified exam in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cycle' => 'string',
            'filiere' => 'string',
            'module' => 'string',
            'date_examen' => 'date',
            'heure_debut' => 'date_format:H:i',
            'heure_fin' => 'date_format:H:i',
            'locaux' => 'string',
            'superviseurs' => 'string',
            'classroom_ids' => 'nullable|array',
            'classroom_ids.*' => 'exists:classrooms,id',
            'superviseur_ids' => 'nullable|array',
            'superviseur_ids.*' => 'exists:superviseurs,id',
            'students' => 'array',
            'students.*.studentId' => 'string',
            'students.*.firstName' => 'string',
            'students.*.lastName' => 'string',
            'students.*.email' => 'email',
            'students.*.program' => 'string'
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

            // Find the exam
            $exam = Exam::findOrFail($id);

            // Update exam details
            $exam->update([
                'cycle' => $request->cycle,
                'filiere' => $request->filiere,
                'module' => $request->module,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'locaux' => $request->locaux,
                'superviseurs' => $request->superviseurs
            ]);

            // Sync classrooms if provided
            if ($request->has('classroom_ids')) {
                // Ensure all classroom IDs are integers
                $classroomIds = is_array($request->classroom_ids) ? array_map('intval', $request->classroom_ids) : [];

                // Sync the classrooms with the exam
                $exam->classrooms()->sync($classroomIds);

                // Log the sync for debugging
                DB::table('logs')->insert([
                    'message' => "Synced classrooms " . implode(', ', $classroomIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Sync supervisors if provided
            if ($request->has('superviseur_ids')) {
                // Ensure all supervisor IDs are integers
                $superviseurIds = is_array($request->superviseur_ids) ? array_map('intval', $request->superviseur_ids) : [];

                // Sync the supervisors with the exam
                $exam->superviseurs()->sync($superviseurIds);

                // Log the sync for debugging
                DB::table('logs')->insert([
                    'message' => "Synced supervisors " . implode(', ', $superviseurIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else if (!empty($request->superviseurs)) {
                // Process supervisors from the superviseurs field
                $superviseurIds = [];
                $superviseursParts = explode(',', $request->superviseurs);

                foreach ($superviseursParts as $part) {
                    $part = trim($part);
                    if (is_numeric($part)) {
                        $superviseurIds[] = (int)$part;
                    } else {
                        // If it's a name, try to find or create the supervisor
                        $nameParts = explode(' ', $part);
                        $lastName = end($nameParts); // Last word is the last name
                        $firstName = implode(' ', array_slice($nameParts, 0, -1)); // Everything else is first name

                        // Try to find the supervisor by name
                        $superviseur = \App\Models\Superviseur::where('nom', $lastName)
                            ->where('prenom', $firstName)
                            ->first();

                        // If not found, create a new supervisor
                        if (!$superviseur) {
                            $superviseur = \App\Models\Superviseur::create([
                                'nom' => $lastName,
                                'prenom' => $firstName,
                                'departement' => $request->filiere, // Use the exam's filiere as the department
                                'type' => 'normal' // Default type
                            ]);

                            // Log the creation for debugging
                            DB::table('logs')->insert([
                                'message' => "Created new supervisor: {$firstName} {$lastName}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        $superviseurIds[] = $superviseur->id;
                    }
                }

                // Sync the supervisors with the exam
                if (!empty($superviseurIds)) {
                    $exam->superviseurs()->sync($superviseurIds);

                    // Log the sync for debugging
                    DB::table('logs')->insert([
                        'message' => "Synced supervisors " . implode(', ', $superviseurIds) . " with exam {$exam->id}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

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

            // Sync students (this will remove any students not in the new list and add new ones)
            $exam->students()->sync($studentIds);

            // Commit the transaction
            DB::commit();

            // Load the updated exam with its relationships
            $exam->load(['students', 'superviseurs']);

            return response()->json([
                'status' => 'success',
                'message' => 'Exam updated successfully',
                'data' => $exam
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
