<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SuperviseurController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ExamClassroomAssignmentController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ExamNotificationController;
use App\Http\Controllers\ProfesseurController;

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
| Formation Routes
|--------------------------------------------------------------------------
*/
Route::get('/formations', [FormationController::class, 'index']);
Route::get('/formations/{id_formation}/filieres', [FormationController::class, 'getFilieresByFormation']);
Route::get('/formations/{id_formation}/filieres/{id_filiere}', [FormationController::class, 'getFormationAndFiliere']);
Route::get('/formations/{id_formation}/filieres/{id_filiere}/modules/{semestre}', [FormationController::class, 'getModulesByFormationAndSemester']);

/*
|--------------------------------------------------------------------------
| Superviseur Routes
|--------------------------------------------------------------------------
*/
Route::get('/superviseurs', [SuperviseurController::class, 'index']);
Route::get('/superviseurs/by-departement', [SuperviseurController::class, 'getByDepartement']);
Route::get('/superviseurs/departements', [SuperviseurController::class, 'getAllDepartements']);

/*
|--------------------------------------------------------------------------
| Departement Routes
|--------------------------------------------------------------------------
*/
Route::get('/departements', [DepartementController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
*/
Route::get('/modules/{id}/name', [ModuleController::class, 'getModuleName']);

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

/*
|--------------------------------------------------------------------------
| Professeur Routes
|--------------------------------------------------------------------------
*/
Route::get('/professeurs/by-departement', [ProfesseurController::class, 'getByDepartement']);
Route::get('/professeurs/departements', [ProfesseurController::class, 'getAllDepartements']);
