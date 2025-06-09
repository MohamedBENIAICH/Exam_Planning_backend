<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Formation;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\Superviseur;
use App\Models\Professeur;
use App\Mail\ExamSurveillanceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="Exams",
 *     description="API Endpoints for managing exams"
 * )
 */
class ExamController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/exams",
     *     summary="Get all exams",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="List of exams retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="formation", type="string"),
     *                     @OA\Property(property="filiere", type="string"),
     *                     @OA\Property(property="module", type="string"),
     *                     @OA\Property(property="semestre", type="string"),
     *                     @OA\Property(property="date_examen", type="string", format="date"),
     *                     @OA\Property(property="heure_debut", type="string", format="time"),
     *                     @OA\Property(property="heure_fin", type="string", format="time"),
     *                     @OA\Property(property="locaux", type="string"),
     *                     @OA\Property(property="superviseurs", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/exams/{id}",
     *     summary="Get a specific exam",
     *     tags={"Exams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Exam ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exam retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="formation", type="string"),
     *                 @OA\Property(property="filiere", type="string"),
     *                 @OA\Property(property="module", type="string"),
     *                 @OA\Property(property="semestre", type="string"),
     *                 @OA\Property(property="date_examen", type="string", format="date"),
     *                 @OA\Property(property="heure_debut", type="string", format="time"),
     *                 @OA\Property(property="heure_fin", type="string", format="time"),
     *                 @OA\Property(property="locaux", type="string"),
     *                 @OA\Property(property="superviseurs", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Exam not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/exams/count",
     *     summary="Get total number of exams",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="Count retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/exams",
     *     summary="Create a new exam",
     *     tags={"Exams"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"formation", "filiere", "module", "semestre", "date_examen", "heure_debut", "heure_fin", "locaux", "superviseurs"},
     *             @OA\Property(property="formation", type="string"),
     *             @OA\Property(property="filiere", type="string"),
     *             @OA\Property(property="module", type="string"),
     *             @OA\Property(property="semestre", type="string"),
     *             @OA\Property(property="date_examen", type="string", format="date"),
     *             @OA\Property(property="heure_debut", type="string", format="time"),
     *             @OA\Property(property="heure_fin", type="string", format="time"),
     *             @OA\Property(property="locaux", type="string"),
     *             @OA\Property(property="superviseurs", type="string"),
     *             @OA\Property(
     *                 property="classroom_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                 property="students",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="studentId", type="string"),
     *                     @OA\Property(property="firstName", type="string"),
     *                     @OA\Property(property="lastName", type="string"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="program", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Exam created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="formation", type="string"),
     *                 @OA\Property(property="filiere", type="string"),
     *                 @OA\Property(property="module", type="string"),
     *                 @OA\Property(property="semestre", type="string"),
     *                 @OA\Property(property="date_examen", type="string", format="date"),
     *                 @OA\Property(property="heure_debut", type="string", format="time"),
     *                 @OA\Property(property="heure_fin", type="string", format="time"),
     *                 @OA\Property(property="locaux", type="string"),
     *                 @OA\Property(property="superviseurs", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Format the date and time fields
            if ($request->has('date_examen')) {
                $request->merge([
                    'date_examen' => date('Y-m-d', strtotime($request->date_examen))
                ]);
            }
            if ($request->has('heure_debut')) {
                $request->merge([
                    'heure_debut' => date('H:i', strtotime($request->heure_debut))
                ]);
            }
            if ($request->has('heure_fin')) {
                $request->merge([
                    'heure_fin' => date('H:i', strtotime($request->heure_fin))
                ]);
            }

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
                'superviseurs' => 'nullable|string|max:255',
                'professeurs' => 'required|string|max:255',
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
                'module_id' => $request->module,
                'semestre' => $request->semestre,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'locaux' => $request->locaux,
                'superviseurs' => $request->superviseurs,
                'professeurs' => $request->professeurs
            ]);

            // Process professors
            if ($request->has('professeurs')) {
                $professeurNames = explode(',', $request->professeurs);
                $professeurIds = [];

                foreach ($professeurNames as $name) {
                    $nameParts = explode(' ', trim($name));
                    if (count($nameParts) >= 2) {
                        $professeur = Professeur::where('prenom', $nameParts[0])
                            ->where('nom', $nameParts[1])
                            ->first();

                        if (!$professeur) {
                            // Create new professor if not exists
                            $professeur = Professeur::create([
                                'nom' => $nameParts[1],
                                'prenom' => $nameParts[0],
                                'departement' => $request->filiere, // Use the exam's filiere as the department
                                'email' => strtolower($nameParts[0] . '.' . $nameParts[1] . '@example.com') // Generate a temporary email
                            ]);
                        }

                        $professeurIds[] = $professeur->id;
                    }
                }

                // Sync professors
                $exam->professeurs()->sync($professeurIds);

                // Log the sync for debugging
                DB::table('logs')->insert([
                    'message' => "Synced professors " . implode(', ', $professeurIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Sync classrooms if provided
            if ($request->has('classroom_ids')) {
                // Ensure all classroom IDs are integers
                $classroomIds = is_array($request->classroom_ids) ? array_map('intval', $request->classroom_ids) : [];

                // Sync the classrooms with the exam
                $exam->classrooms()->sync($classroomIds);

                // Create schedule entries for each classroom
                foreach ($classroomIds as $classroomId) {
                    \App\Models\ClassroomExamSchedule::create([
                        'classroom_id' => $classroomId,
                        'exam_id' => $exam->id,
                        'date_examen' => $request->date_examen,
                        'heure_debut' => $request->heure_debut,
                        'heure_fin' => $request->heure_fin,
                    ]);
                }

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
            $exam->load(['students', 'module', 'classrooms']);

            // Send notifications to supervisors
            if ($request->has('superviseurs')) {
                $superviseurNames = explode(',', $request->superviseurs);
                foreach ($superviseurNames as $name) {
                    $nameParts = explode(' ', trim($name));
                    if (count($nameParts) >= 2) {
                        $superviseur = Superviseur::where('prenom', $nameParts[0])
                            ->where('nom', $nameParts[1])
                            ->first();

                        if ($superviseur && $superviseur->email) {
                            try {
                                Mail::to($superviseur->email)
                                    ->send(new ExamSurveillanceNotification($exam, $name));

                                Log::info("Notification envoyée au superviseur: {$name} ({$superviseur->email})");
                            } catch (\Exception $e) {
                                Log::error("Erreur d'envoi d'email au superviseur {$name}: " . $e->getMessage());
                            }
                        } else {
                            Log::warning("Superviseur non trouvé ou email manquant: {$name}");
                        }
                    }
                }
            }

            // Send notifications to professors
            if ($request->has('professeurs')) {
                $professeurNames = explode(',', $request->professeurs);
                foreach ($professeurNames as $name) {
                    $nameParts = explode(' ', trim($name));
                    if (count($nameParts) >= 2) {
                        $professeur = Professeur::where('prenom', $nameParts[0])
                            ->where('nom', $nameParts[1])
                            ->first();

                        if ($professeur && $professeur->email) {
                            try {
                                Mail::to($professeur->email)
                                    ->send(new ExamSurveillanceNotification($exam, $name));

                                Log::info("Notification envoyée au professeur: {$name} ({$professeur->email})");
                            } catch (\Exception $e) {
                                Log::error("Erreur d'envoi d'email au professeur {$name}: " . $e->getMessage());
                            }
                        } else {
                            Log::warning("Professeur non trouvé ou email manquant: {$name}");
                        }
                    }
                }
            }

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
     * @OA\Delete(
     *     path="/api/exams/{id}",
     *     summary="Delete an exam",
     *     tags={"Exams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Exam ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exam deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Exam not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/exams/latest",
     *     summary="Get the last 5 exams created",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="Latest exams retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="formation", type="string"),
     *                     @OA\Property(property="filiere", type="string"),
     *                     @OA\Property(property="module", type="string"),
     *                     @OA\Property(property="semestre", type="string"),
     *                     @OA\Property(property="date_examen", type="string", format="date"),
     *                     @OA\Property(property="heure_debut", type="string", format="time"),
     *                     @OA\Property(property="heure_fin", type="string", format="time"),
     *                     @OA\Property(property="locaux", type="string"),
     *                     @OA\Property(property="superviseurs", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/exams/{id}",
     *     summary="Update an existing exam",
     *     tags={"Exams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Exam ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cycle", type="string"),
     *             @OA\Property(property="filiere", type="string"),
     *             @OA\Property(property="module", type="string"),
     *             @OA\Property(property="date_examen", type="string", format="date"),
     *             @OA\Property(property="heure_debut", type="string", format="time"),
     *             @OA\Property(property="heure_fin", type="string", format="time"),
     *             @OA\Property(property="locaux", type="string"),
     *             @OA\Property(property="superviseurs", type="string"),
     *             @OA\Property(
     *                 property="classroom_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                 property="superviseur_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             ),
     *             @OA\Property(
     *                 property="students",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="studentId", type="string"),
     *                     @OA\Property(property="firstName", type="string"),
     *                     @OA\Property(property="lastName", type="string"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="program", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exam updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="formation", type="string"),
     *                 @OA\Property(property="filiere", type="string"),
     *                 @OA\Property(property="module", type="string"),
     *                 @OA\Property(property="semestre", type="string"),
     *                 @OA\Property(property="date_examen", type="string", format="date"),
     *                 @OA\Property(property="heure_debut", type="string", format="time"),
     *                 @OA\Property(property="heure_fin", type="string", format="time"),
     *                 @OA\Property(property="locaux", type="string"),
     *                 @OA\Property(property="superviseurs", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Exam not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Format the date and time fields
            if ($request->has('date_examen')) {
                $request->merge([
                    'date_examen' => date('Y-m-d', strtotime($request->date_examen))
                ]);
            }
            if ($request->has('heure_debut')) {
                $request->merge([
                    'heure_debut' => date('H:i', strtotime($request->heure_debut))
                ]);
            }
            if ($request->has('heure_fin')) {
                $request->merge([
                    'heure_fin' => date('H:i', strtotime($request->heure_fin))
                ]);
            }

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
                'superviseurs' => 'nullable|string|max:255',
                'professeurs' => 'required|string|max:255',
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
            // Find the exam
            $exam = Exam::findOrFail($id);

            // Update exam details
            $exam->update([
                'formation' => $request->formation,
                'filiere' => $request->filiere,
                'module_id' => $request->module,
                'semestre' => $request->semestre,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'locaux' => $request->locaux,
                'superviseurs' => $request->superviseurs,
                'professeurs' => $request->professeurs
            ]);

            // Sync classrooms if provided
            if ($request->has('classroom_ids')) {
                $classroomIds = is_array($request->classroom_ids)
                    ? array_map('intval', $request->classroom_ids)
                    : [];

                $exam->classrooms()->sync($classroomIds);

                // Create schedule entries for each classroom
                foreach ($classroomIds as $classroomId) {
                    \App\Models\ClassroomExamSchedule::create([
                        'classroom_id' => $classroomId,
                        'exam_id' => $exam->id,
                        'date_examen' => $request->date_examen,
                        'heure_debut' => $request->heure_debut,
                        'heure_fin' => $request->heure_fin,
                    ]);
                }

                // Log the sync
                DB::table('logs')->insert([
                    'message' => "Synced classrooms " . implode(', ', $classroomIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Sync supervisors
            if ($request->has('superviseur_ids')) {
                $superviseurIds = is_array($request->superviseur_ids)
                    ? array_map('intval', $request->superviseur_ids)
                    : [];

                $exam->superviseurs()->sync($superviseurIds);

                // Log the sync
                DB::table('logs')->insert([
                    'message' => "Synced supervisors " . implode(', ', $superviseurIds) . " with exam {$exam->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Process students
            foreach ($request->students as $studentData) {
                $student = Student::firstOrCreate([
                    'numero_etudiant' => $studentData['studentId']
                ], [
                    'nom' => $studentData['lastName'],
                    'prenom' => $studentData['firstName'],
                    'email' => $studentData['email'],
                    'filiere' => $studentData['program'],
                    'niveau' => 'L3'
                ]);

                $studentIds[] = $student->id;
            }

            // Sync students
            $exam->students()->sync($studentIds);

            // Commit the transaction
            DB::commit();

            // Load the updated exam with relationships
            $exam->load(['students', 'superviseurs']);

            // Send notifications to supervisors
            app(\App\Services\ExamNotificationService::class)->sendSupervisorNotifications($exam);

            return response()->json([
                'status' => 'success',
                'message' => 'Exam updated successfully',
                'data' => $exam
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            DB::table('logs')->insert([
                'message' => "Error updating exam {$id}: " . $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/exams/upcoming",
     *     summary="Get all upcoming exams",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="List of upcoming exams retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="formation", type="string"),
     *                     @OA\Property(property="filiere", type="string"),
     *                     @OA\Property(property="module", type="string"),
     *                     @OA\Property(property="semestre", type="string"),
     *                     @OA\Property(property="date_examen", type="string", format="date"),
     *                     @OA\Property(property="heure_debut", type="string", format="time"),
     *                     @OA\Property(property="heure_fin", type="string", format="time"),
     *                     @OA\Property(property="locaux", type="string"),
     *                     @OA\Property(property="superviseurs", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getUpcomingExams()
    {
        try {
            $today = now()->toDateString();
            $exams = Exam::where('date_examen', '>=', $today)
                ->orderBy('date_examen', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $exams
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve upcoming exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/exams/passed",
     *     summary="Get all passed exams",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="List of passed exams retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="formation", type="string"),
     *                     @OA\Property(property="filiere", type="string"),
     *                     @OA\Property(property="module", type="string"),
     *                     @OA\Property(property="semestre", type="string"),
     *                     @OA\Property(property="date_examen", type="string", format="date"),
     *                     @OA\Property(property="heure_debut", type="string", format="time"),
     *                     @OA\Property(property="heure_fin", type="string", format="time"),
     *                     @OA\Property(property="locaux", type="string"),
     *                     @OA\Property(property="superviseurs", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getPassedExams()
    {
        try {
            $today = now()->toDateString();
            $exams = Exam::where('date_examen', '<', $today)
                ->orderBy('date_examen', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $exams,
                'count' => $exams->count()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getPassedExams: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve passed exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/exams/with-names",
     *     summary="Get all exams with names instead of IDs",
     *     tags={"Exams"},
     *     @OA\Response(
     *         response=200,
     *         description="List of exams with names retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="formation_name", type="string", example="Licence en Informatique"),
     *                     @OA\Property(property="filiere_name", type="string", example="Informatique"),
     *                     @OA\Property(property="module_name", type="string", example="Programmation Web"),
     *                     @OA\Property(property="semestre", type="string", example="S5"),
     *                     @OA\Property(property="date_examen", type="string", format="date", example="2024-04-20"),
     *                     @OA\Property(property="heure_debut", type="string", format="time", example="09:00"),
     *                     @OA\Property(property="heure_fin", type="string", format="time", example="11:00"),
     *                     @OA\Property(property="locaux", type="string", example="Amphi A"),
     *                     @OA\Property(property="superviseurs", type="string", example="Dr. Smith, Dr. Johnson")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve exams with names"),
     *             @OA\Property(property="error", type="string", example="Error message details")
     *         )
     *     )
     * )
     */
    public function getExamsWithNames()
    {
        try {
            // Fetch exams with their related data
            $exams = Exam::with(['formation', 'filiere', 'module'])->get();

            // Transform the data to include names instead of IDs
            $transformedExams = $exams->map(function ($exam) {
                // Get the related models
                $formation = Formation::find($exam->formation);
                $filiere = Filiere::find($exam->filiere);
                $module = Module::find($exam->module_id);

                return [
                    'id' => $exam->id,
                    'formation_name' => $formation ? $formation->formation_intitule : null,
                    'filiere_name' => $filiere ? $filiere->filiere_intitule : null,
                    'module_name' => $module ? $module->module_intitule : null,
                    'semestre' => $exam->semestre,
                    'date_examen' => date('Y-m-d', strtotime($exam->date_examen)),
                    'heure_debut' => date('H:i', strtotime($exam->heure_debut)),
                    'heure_fin' => date('H:i', strtotime($exam->heure_fin)),
                    'locaux' => $exam->locaux,
                    'superviseurs' => $exam->superviseurs
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedExams
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getExamsWithNames: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve exams with names',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/exams/{id}/download-pdf",
     *     summary="Download exam PDF with student list",
     *     tags={"Exams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Exam ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF generated and downloaded successfully",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Exam not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function downloadPdf($id)
    {
        try {
            // Récupérer l'examen avec toutes les relations nécessaires
            $exam = Exam::with([
                'formation',
                'filiere',
                'module',
                'students',
                'superviseurs',
                'professeurs'
            ])->findOrFail($id);

            // Debug: Vérifier les données récupérées
            Log::info('Exam data for PDF generation:', [
                'exam_id' => $exam->id,
                'students_type' => gettype($exam->students),
                'students_count' => is_object($exam->students) ? $exam->students->count() : 'not a collection',
                'superviseurs_type' => gettype($exam->superviseurs),
                'superviseurs_value' => $exam->superviseurs,
                'professeurs_type' => gettype($exam->professeurs),
                'professeurs_value' => $exam->professeurs
            ]);

            // Récupérer les données des relations
            $formation = Formation::find($exam->formation);
            $filiere = Filiere::find($exam->filiere);
            $module = Module::find($exam->module_id);

            // S'assurer que students est une collection
            $students = collect();
            if ($exam->students && is_object($exam->students) && method_exists($exam->students, 'count')) {
                $students = $exam->students;
            } else {
                // Si ce n'est pas une collection, essayer de récupérer les étudiants manuellement
                $students = Student::whereHas('exams', function ($query) use ($exam) {
                    $query->where('exam_id', $exam->id);
                })->get();
            }

            // S'assurer que superviseurs est une collection
            $superviseurs = collect();
            if ($exam->superviseurs) {
                if (is_object($exam->superviseurs) && method_exists($exam->superviseurs, 'count')) {
                    // Si c'est une relation many-to-many
                    $superviseurs = $exam->superviseurs;
                } else if (is_string($exam->superviseurs) && !empty($exam->superviseurs)) {
                    // Si c'est une chaîne de caractères (noms séparés par des virgules)
                    $superviseurNames = explode(',', $exam->superviseurs);
                    $superviseurs = collect($superviseurNames)->map(function ($name) {
                        $nameParts = explode(' ', trim($name));
                        return (object) [
                            'nom' => isset($nameParts[0]) ? $nameParts[0] : trim($name),
                            'prenom' => isset($nameParts[1]) ? $nameParts[1] : '',
                            'email' => ''
                        ];
                    });
                } else {
                    // Essayer de récupérer via la relation many-to-many si elle existe
                    try {
                        $superviseurs = Superviseur::whereHas('exams', function ($query) use ($exam) {
                            $query->where('exam_id', $exam->id);
                        })->get();
                    } catch (\Exception $e) {
                        // Si la relation n'existe pas, on garde une collection vide
                        $superviseurs = collect();
                    }
                }
            }

            // S'assurer que professeurs est une collection
            $professeurs = collect();
            if ($exam->professeurs) {
                if (is_object($exam->professeurs) && method_exists($exam->professeurs, 'count')) {
                    // Si c'est une relation many-to-many
                    $professeurs = $exam->professeurs;
                } else if (is_string($exam->professeurs) && !empty($exam->professeurs)) {
                    // Si c'est une chaîne de caractères (noms séparés par des virgules)
                    $professeurNames = explode(',', $exam->professeurs);
                    $professeurs = collect($professeurNames)->map(function ($name) {
                        $nameParts = explode(' ', trim($name));
                        return (object) [
                            'nom' => isset($nameParts[0]) ? $nameParts[0] : trim($name),
                            'prenom' => isset($nameParts[1]) ? $nameParts[1] : '',
                            'email' => ''
                        ];
                    });
                } else {
                    // Essayer de récupérer via la relation many-to-many si elle existe
                    try {
                        $professeurs = Professeur::whereHas('exams', function ($query) use ($exam) {
                            $query->where('exam_id', $exam->id);
                        })->get();
                    } catch (\Exception $e) {
                        // Si la relation n'existe pas, on garde une collection vide
                        $professeurs = collect();
                    }
                }
            }

            // Préparer les données pour le PDF
            $data = [
                'exam' => $exam,
                'formation' => $formation,
                'filiere' => $filiere,
                'module' => $module,
                'students' => $students,
                'superviseurs' => $superviseurs,
                'professeurs' => $professeurs,
                'date_examen' => \Carbon\Carbon::parse($exam->date_examen)->format('d/m/Y'),
                'heure_debut' => \Carbon\Carbon::parse($exam->heure_debut)->format('H:i'),
                'heure_fin' => \Carbon\Carbon::parse($exam->heure_fin)->format('H:i'),
            ];

            // Debug: Vérifier les données finales
            Log::info('Final data for PDF template:', [
                'students_count' => $data['students']->count(),
                'superviseurs_count' => $data['superviseurs']->count(),
                'professeurs_count' => $data['professeurs']->count()
            ]);

            // Générer le PDF
            $pdf = PDF::loadView('pdfs.exam-convocation', $data);

            // Configurer le PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            // Nom du fichier (on nettoie la date et le nom du module)
            $moduleName = $module ? preg_replace('/[\\\\\/\:"\*\?<>\|]+/', '-', $module->module_intitule) : 'Module';
            $dateExamen = preg_replace('/[\\\\\/\:"\*\?<>\|]+/', '-', $data['date_examen']);
            $filename = 'Convocation_Examen_' . $moduleName . '_' . $dateExamen . '.pdf';

            // Télécharger le PDF
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating exam PDF: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
