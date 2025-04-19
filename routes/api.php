<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SuperviseurController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Exam routes
Route::get('/exams', [ExamController::class, 'index']);
Route::get('/exams/count', [ExamController::class, 'count']);
Route::get('/exams/latest', [ExamController::class, 'getLatestExams']);
Route::get('/exams/{id}', [ExamController::class, 'show']);
Route::post('/exams', [ExamController::class, 'store']);
Route::put('/exams/{id}', [ExamController::class, 'update']);
Route::delete('/exams/{id}', [ExamController::class, 'destroy']);

// Student routes
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/count', [StudentController::class, 'count']);

// Classroom routes
Route::get('/classrooms', [ClassroomController::class, 'index']);
Route::get('/classrooms/count', [ClassroomController::class, 'count']);
Route::get('/classrooms/available/count', [ClassroomController::class, 'availableCount']);
Route::get('/classrooms/available', [ClassroomController::class, 'available']);
Route::get('/classrooms/{id}', [ClassroomController::class, 'show']);
Route::post('/classrooms', [ClassroomController::class, 'store']);
Route::put('/classrooms/{id}', [ClassroomController::class, 'update']);
Route::delete('/classrooms/{id}', [ClassroomController::class, 'destroy']);

// Test route
Route::get('/test-classroom-exam', [TestController::class, 'testClassroomExamRelationship']);

// Test route for creating an exam with classroom_ids
Route::post('/test-create-exam-with-classrooms', function (Request $request) {
    try {
        // Create a test classroom if it doesn't exist
        $classroom = \App\Models\Classroom::firstOrCreate(
            ['nom_du_local' => 'Test Classroom'],
            [
                'departement' => 'Test Department',
                'capacite' => 30,
                'liste_des_equipements' => ['projector', 'whiteboard'],
                'disponible_pour_planification' => true
            ]
        );

        // Create a test exam
        $exam = \App\Models\Exam::create([
            'cycle' => 'Test Cycle',
            'filiere' => 'Test Filiere',
            'module' => 'Test Module',
            'date_examen' => now(),
            'heure_debut' => now(),
            'heure_fin' => now()->addHours(2),
            'locaux' => 'Test Locaux',
            'superviseurs' => 'Test Superviseurs'
        ]);

        // Attach the classroom to the exam
        $exam->classrooms()->attach($classroom->id);

        // Check if the relationship was created
        $classroomExamCount = \Illuminate\Support\Facades\DB::table('classroom_exam')
            ->where('exam_id', $exam->id)
            ->where('classroom_id', $classroom->id)
            ->count();

        // Get the exam with its classrooms
        $examWithClassrooms = \App\Models\Exam::with('classrooms')->find($exam->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Test completed successfully',
            'classroom_exam_count' => $classroomExamCount,
            'exam' => $examWithClassrooms,
            'classroom' => $classroom
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Test failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Supervisor routes
Route::get('/superviseurs/by-departement', [SuperviseurController::class, 'getByDepartement']);
Route::get('/superviseurs/departements', [SuperviseurController::class, 'getAllDepartements']);
