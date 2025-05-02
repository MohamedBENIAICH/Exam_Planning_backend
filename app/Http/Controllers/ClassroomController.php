<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ClassroomExamSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the classrooms.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $classrooms = Classroom::all();

            return response()->json([
                'status' => 'success',
                'data' => $classrooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the total number of classrooms in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        try {
            $count = Classroom::count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // /**
    //  * Get the number of available classrooms for scheduling.
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function availableCount()
    // {
    //     try {
    //         $count = Classroom::where('disponible_pour_planification', true)->count();

    //         return response()->json([
    //             'status' => 'success',
    //             'count' => $count
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to count available classrooms',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Store a newly created classroom in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_du_local' => 'required|string|max:255',
            'departement' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'liste_des_equipements' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $classroom = Classroom::create($request->except('disponible_pour_planification'));
        return response()->json($classroom, 201);
    }

    /**
     * Display the specified classroom.
     */
    public function show($id)
    {
        try {
            $classroom = Classroom::findOrFail($id);
            return response()->json($classroom);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified classroom in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom_du_local' => 'string|max:255',
            'departement' => 'string|max:255',
            'capacite' => 'integer|min:1',
            'liste_des_equipements' => 'nullable|array',
            // 'disponible_pour_planification' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->update($request->all());
            return response()->json($classroom);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified classroom from storage.
     */
    public function destroy($id)
    {
        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available classrooms for scheduling.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function available()
    {
        try {
            $classrooms = Classroom::all();

            return response()->json([
                'status' => 'success',
                'data' => $classrooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve available classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function updateDisponibilite(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'disponible_pour_planification' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     try {
    //         $classroom = Classroom::findOrFail($id);
    //         $classroom->disponible_pour_planification = $request->input('disponible_pour_planification');
    //         $classroom->save();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Disponibilité mise à jour avec succès',
    //             'classroom' => $classroom
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Échec de la mise à jour de la disponibilité',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Schedule an exam in a classroom.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scheduleExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classroom_id' => 'required|exists:classrooms,id',
            'exam_id' => 'required|exists:exams,id',
            'date_examen' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if classroom is available for the given time slot
            $isAvailable = !ClassroomExamSchedule::where('classroom_id', $request->classroom_id)
                ->where('date_examen', $request->date_examen)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('heure_debut', '<=', $request->heure_debut)
                            ->where('heure_fin', '>', $request->heure_debut);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('heure_debut', '<', $request->heure_fin)
                            ->where('heure_fin', '>=', $request->heure_fin);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('heure_debut', '>=', $request->heure_debut)
                            ->where('heure_fin', '<=', $request->heure_fin);
                    });
                })
                ->exists();

            if (!$isAvailable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Classroom is not available for the specified time slot'
                ], 409);
            }

            // Create the schedule
            $schedule = ClassroomExamSchedule::create([
                'classroom_id' => $request->classroom_id,
                'exam_id' => $request->exam_id,
                'date_examen' => $request->date_examen,
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Exam scheduled successfully',
                'data' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to schedule exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // /**
    //  * Get available classrooms for a specific date and time slot.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function getAvailableClassrooms(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'date_examen' => 'required|date',
    //         'heure_debut' => 'required|date_format:H:i',
    //         'heure_fin' => 'required|date_format:H:i|after:heure_debut',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         // Get all classrooms that are not scheduled for the given time slot
    //         $availableClassrooms = Classroom::where('disponible_pour_planification', true)
    //             ->whereNotIn('id', function ($query) use ($request) {
    //                 $query->select('classroom_id')
    //                     ->from('classroom_exam_schedule')
    //                     ->where('date_examen', $request->date_examen)
    //                     ->where(function ($q) use ($request) {
    //                         $q->where(function ($subq) use ($request) {
    //                             $subq->where('heure_debut', '<=', $request->heure_debut)
    //                                 ->where('heure_fin', '>', $request->heure_debut);
    //                         })->orWhere(function ($subq) use ($request) {
    //                             $subq->where('heure_debut', '<', $request->heure_fin)
    //                                 ->where('heure_fin', '>=', $request->heure_fin);
    //                         })->orWhere(function ($subq) use ($request) {
    //                             $subq->where('heure_debut', '>=', $request->heure_debut)
    //                                 ->where('heure_fin', '<=', $request->heure_fin);
    //                         });
    //                     });
    //             })
    //             ->get();

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $availableClassrooms
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to retrieve available classrooms',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Get a classroom by its name.
     *
     * @param string $classroomName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByName($classroomName)
    {
        try {
            $classroom = Classroom::where('nom_du_local', $classroomName)->first();

            if (!$classroom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Classroom not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $classroom
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get classrooms by date and time range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassroomsByDateTime(Request $request)
    {
        try {
            // Log the request
            \Illuminate\Support\Facades\Log::info('=== CLASSROOM SEARCH START ===');
            \Illuminate\Support\Facades\Log::info('Request data:', $request->all());

            $validator = Validator::make($request->all(), [
                'date_examen' => 'required|date',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            ]);

            if ($validator->fails()) {
                \Illuminate\Support\Facades\Log::error('Validation failed:', [
                    'errors' => $validator->errors(),
                    'input' => $request->all()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Format the time values to match the database time format
            $heureDebut = date('H:i:s', strtotime($request->heure_debut));
            $heureFin = date('H:i:s', strtotime($request->heure_fin));

            // Get classroom IDs that are scheduled for the given time slot
            $query = DB::table('classroom_exam_schedule')
                ->where('date_examen', $request->date_examen)
                ->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where(function ($subq) use ($heureDebut, $heureFin) {
                        $subq->where('heure_debut', '<=', $heureDebut)
                            ->where('heure_fin', '>', $heureDebut);
                    })->orWhere(function ($subq) use ($heureDebut, $heureFin) {
                        $subq->where('heure_debut', '<', $heureFin)
                            ->where('heure_fin', '>=', $heureFin);
                    })->orWhere(function ($subq) use ($heureDebut, $heureFin) {
                        $subq->where('heure_debut', '>=', $heureDebut)
                            ->where('heure_fin', '<=', $heureFin);
                    });
                });

            // Log the SQL query and its bindings
            \Illuminate\Support\Facades\Log::info('SQL Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'formatted_times' => [
                    'heure_debut' => $heureDebut,
                    'heure_fin' => $heureFin
                ]
            ]);

            $scheduledClassroomIds = $query->pluck('classroom_id')->toArray();

            // Log the results
            \Illuminate\Support\Facades\Log::info('Search results:', [
                'count' => count($scheduledClassroomIds),
                'ids' => $scheduledClassroomIds
            ]);
            \Illuminate\Support\Facades\Log::info('=== CLASSROOM SEARCH END ===');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'scheduled_classroom_ids' => $scheduledClassroomIds
                ]
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getClassroomsByDateTime:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve scheduled classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get classrooms that are not in the provided list of IDs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassroomsNotInList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classroom_ids' => 'nullable|array',
            'classroom_ids.*' => 'integer|exists:classrooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $classroomIds = $request->input('classroom_ids', []);

            // Check if the list is empty
            if (empty($classroomIds)) {
                // Return all classrooms if the list is empty
                $classrooms = Classroom::all();
            } else {
                // Return classrooms not in the provided list
                $classrooms = Classroom::whereNotIn('id', $classroomIds)->get();
            }

            return response()->json([
                'status' => 'success',
                'data' => $classrooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
