<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassroomController;

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
