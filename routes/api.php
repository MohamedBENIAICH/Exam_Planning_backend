<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SuperviseurController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ExamClassroomAssignmentController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ExamNotificationController;
use App\Http\Controllers\ProfesseurController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\ConcoursController;
use App\Http\Controllers\SetupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
*/

// Auth route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Exam Routes
|--------------------------------------------------------------------------
*/
Route::prefix('exams')->group(function () {
    // Specific routes first
    Route::get('/with-names', [ExamController::class, 'getExamsWithNames']);
    Route::get('/count', [ExamController::class, 'count']);
    Route::get('/latest', [ExamController::class, 'getLatestExams']);
    Route::get('/upcoming', [ExamController::class, 'getUpcomingExams']);
    Route::get('/passed', [ExamController::class, 'getPassedExams']);
    Route::get('/count-passed', [ExamController::class, 'countPassedExams']);
    Route::get('/count-upcoming', [ExamController::class, 'countUpcomingExams']);

    // PDF download route
    Route::get('/{id}/download-pdf', [ExamController::class, 'downloadPdf']);

    // Standard CRUD routes
    Route::get('/', [ExamController::class, 'index']);
    Route::post('/', [ExamController::class, 'store']);
    Route::get('/{id}', [ExamController::class, 'show']);
    Route::put('/{id}', [ExamController::class, 'update']);
    Route::delete('/{id}', [ExamController::class, 'destroy']);

    // Exam-Classroom Assignment
    Route::post('{exam_id}/assignments', [ExamClassroomAssignmentController::class, 'store']);
    Route::get('{exam_id}/assignments', [ExamClassroomAssignmentController::class, 'show']);
    Route::delete('{exam_id}/assignments', [ExamClassroomAssignmentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/count', [StudentController::class, 'count']);
Route::get('/students/by-exam/{examId}', [StudentController::class, 'getStudentsByExamId']);

/*
|--------------------------------------------------------------------------
| Classroom Routes
|--------------------------------------------------------------------------
*/
Route::prefix('classrooms')->group(function () {
    // Specific routes first to avoid conflicts
    Route::get('/by-datetime', [ClassroomController::class, 'getClassroomsByDateTime']);
    Route::get('/available', [ClassroomController::class, 'available']);
    Route::get('/available-count', [ClassroomController::class, 'availableCount']);
    Route::get('/count', [ClassroomController::class, 'count']);
    Route::get('/name/{classroomName}', [ClassroomController::class, 'getByName']);
    Route::get('/available-for-slot', [ClassroomController::class, 'getAvailableClassrooms']);
    Route::post('/schedule-exam', [ClassroomController::class, 'scheduleExam']);
    Route::post('/not-in-list', [ClassroomController::class, 'getClassroomsNotInList']);
    Route::get('/amphitheaters', [ClassroomController::class, 'getAmphitheaters']);

    // Standard CRUD
    Route::get('/', [ClassroomController::class, 'index']);
    Route::post('/', [ClassroomController::class, 'store']);
    Route::get('/{id}', [ClassroomController::class, 'show']);
    Route::put('/{id}', [ClassroomController::class, 'update']);
    Route::delete('/{id}', [ClassroomController::class, 'destroy']);
    Route::put('/{id}/disponibilite', [ClassroomController::class, 'updateDisponibilite']);
});
/*
|--------------------------------------------------------------------------
| Filiere Routes
|--------------------------------------------------------------------------
*/
Route::prefix('filieres')->group(function () {
    Route::get('/', [FiliereController::class, 'index']);
    Route::get('/filieres-with-details', [FiliereController::class, 'filieresWithDepartementAndFormationNames']);
    Route::post('/', [FiliereController::class, 'store']);
    Route::get('/{id}', [FiliereController::class, 'show']);
    Route::put('/{id}', [FiliereController::class, 'update']);
    Route::delete('/{id}', [FiliereController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Formation Routes
|--------------------------------------------------------------------------
*/
Route::get('/formations', [FormationController::class, 'index']);
Route::get('/formations/{id_formation}/filieres', [FormationController::class, 'getFilieresByFormation']);
Route::get('/formations/{id_formation}/filieres/{id_filiere}', [FormationController::class, 'getFormationAndFiliere']);
Route::get('/formations/{id_formation}/filieres/{id_filiere}/modules/{semestre}', [FormationController::class, 'getModulesByFormationAndSemester']);
Route::post('/formations', [FormationController::class, 'store']);
Route::delete('/formations/{id}', [FormationController::class, 'delete']);
Route::put('/formations/{id}', [FormationController::class, 'update']);
Route::get('/formations/{id}', [FormationController::class, 'show']);
/*
|--------------------------------------------------------------------------
| Superviseur Routes
|--------------------------------------------------------------------------
*/
Route::get('/superviseurs', [SuperviseurController::class, 'index']);
Route::get('/superviseurs/by-service', [SuperviseurController::class, 'getByService']);
Route::get('/superviseurs/service', [SuperviseurController::class, 'getAllServices']);
Route::post('/superviseurs', [SuperviseurController::class, 'store']);
Route::delete('/superviseurs/{id}', [SuperviseurController::class, 'destroy']);
Route::put('/superviseurs/{id}', [SuperviseurController::class, 'update']);
/*
|--------------------------------------------------------------------------
| Professor Routes
|--------------------------------------------------------------------------
*/
Route::prefix('professeurs')->group(function () {
    Route::get('/', [ProfesseurController::class, 'index']);
    Route::post('/', [ProfesseurController::class, 'store']);
    Route::get('/count', [ProfesseurController::class, 'count']);
    Route::get('/by-departement', [ProfesseurController::class, 'getByDepartement']);
    Route::get('/departements', [ProfesseurController::class, 'getAllDepartements']);
    Route::delete('/{id}', [ProfesseurController::class, 'destroy']);
    Route::put('/{id}', [ProfesseurController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| Departement Routes
|--------------------------------------------------------------------------
*/
Route::get('/departements', [DepartementController::class, 'index']);
Route::get('/departements/{id}', [DepartementController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
*/

Route::prefix('modules')->group(function () {
    Route::get('modules-with-filieres-formation', [ModuleController::class, 'modulesWithFilieresAndFormationName']);
    Route::get('/{id}/name', [ModuleController::class, 'getModuleName']);
    Route::get('/', [ModuleController::class, 'index']);
    Route::post('/', [ModuleController::class, 'store']);
    Route::get('/{id}', [ModuleController::class, 'show']);
    Route::put('/{id}', [ModuleController::class, 'update']);
    Route::delete('/{id}', [ModuleController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
*/
Route::get('/test-classroom-exam', [TestController::class, 'testClassroomExamRelationship']);

// Create Exam with Classroom test
Route::post('/test-create-exam-with-classrooms', function (Request $request) {
    try {
        $classroom = \App\Models\Classroom::firstOrCreate(
            ['nom_du_local' => 'Test Classroom'],
            [
                'departement' => 'Test Department',
                'capacite' => 30,
                'liste_des_equipements' => ['projector', 'whiteboard'],
                'disponible_pour_planification' => true,
            ]
        );

        $exam = \App\Models\Exam::create([
            'cycle' => 'Test Cycle',
            'filiere' => 'Test Filiere',
            'module' => 'Test Module',
            'date_examen' => now(),
            'heure_debut' => now(),
            'heure_fin' => now()->addHours(2),
            'locaux' => 'Test Locaux',
            'superviseurs' => 'Test Superviseurs',
        ]);

        $exam->classrooms()->attach($classroom->id);

        $classroomExamCount = \Illuminate\Support\Facades\DB::table('classroom_exam')
            ->where('exam_id', $exam->id)
            ->where('classroom_id', $classroom->id)
            ->count();

        $examWithClassrooms = \App\Models\Exam::with('classrooms')->find($exam->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Test completed successfully',
            'classroom_exam_count' => $classroomExamCount,
            'exam' => $examWithClassrooms,
            'classroom' => $classroom,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Test failed',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::post('/exams/{exam}/send-invitations', [ExamNotificationController::class, 'sendNotifications']);

Route::get('/api/documentation', '\L5Swagger\Http\Controllers\SwaggerController@api')->name('l5-swagger.default.docs');

/*
|--------------------------------------------------------------------------
| Candidat Routes
|--------------------------------------------------------------------------
*/
Route::prefix('candidats')->group(function () {
    Route::get('/', [CandidatController::class, 'index']);
    Route::post('/', [CandidatController::class, 'store']);
    Route::get('/{id}', [CandidatController::class, 'show']);
    Route::put('/{id}', [CandidatController::class, 'update']);
    Route::delete('/{id}', [CandidatController::class, 'destroy']);
});
/*
|--------------------------------------------------------------------------
| Concours Routes
|--------------------------------------------------------------------------
*/
Route::prefix('concours')->group(function () {
    Route::get('/', [ConcoursController::class, 'index']);
    Route::post('/', [ConcoursController::class, 'store']);
    Route::get('/latest', [ConcoursController::class, 'latest']);
    Route::get('/{id}', [ConcoursController::class, 'show']);
    Route::put('/{id}', [ConcoursController::class, 'update']);
    Route::delete('/{id}', [ConcoursController::class, 'destroy']);

    // Routes pour les convocations
    Route::post('/{id}/send-convocations', [ConcoursController::class, 'sendConvocations']);
    Route::post('/{id}/send-surveillance-notifications', [ConcoursController::class, 'sendSurveillanceNotifications']);
    Route::get('/{id}/download-report', [ConcoursController::class, 'downloadReport']);
});

Route::get('/test-public', function () {
    return response()->json(['status' => 'ok']);
});

Route::fallback(function () {
    return response()->json(['message' => 'API route not found.'], 404);
});

/*
|--------------------------------------------------------------------------
| Setup Routes
|--------------------------------------------------------------------------
*/
Route::prefix('setup')->group(function () {
    Route::post('/create-tables', [SetupController::class, 'createMissingTables']);
    Route::get('/check-tables', [SetupController::class, 'checkTables']);
});
