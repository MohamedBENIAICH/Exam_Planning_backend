<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function testClassroomExamRelationship()
    {
        try {
            // Create a test classroom if it doesn't exist
            $classroom = Classroom::firstOrCreate(
                ['nom_du_local' => 'Test Classroom'],
                [
                    'departement' => 'Test Department',
                    'capacite' => 30,
                    'liste_des_equipements' => ['projector', 'whiteboard'],
                    'disponible_pour_planification' => true
                ]
            );

            // Create a test exam
            $exam = Exam::create([
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
            $classroomExamCount = DB::table('classroom_exam')
                ->where('exam_id', $exam->id)
                ->where('classroom_id', $classroom->id)
                ->count();

            // Get the exam with its classrooms
            $examWithClassrooms = Exam::with('classrooms')->find($exam->id);

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
    }
}
