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

/**
 * @OA\Tag(
 *     name="Classrooms",
 *     description="API Endpoints for managing classrooms"
 * )
 */
class ClassroomController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/classrooms",
     *     summary="Get all classrooms",
     *     tags={"Classrooms"},
     *     @OA\Response(
     *         response=200,
     *         description="List of classrooms",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom_du_local", type="string", example="Amphi A"),
     *                     @OA\Property(property="departement", type="string", example="Informatique"),
     *                     @OA\Property(property="capacite", type="integer", example=100),
     *                     @OA\Property(property="liste_des_equipements", type="array", @OA\Items(type="string"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve classrooms"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/classrooms/count",
     *     summary="Get total number of classrooms",
     *     tags={"Classrooms"},
     *     @OA\Response(
     *         response=200,
     *         description="Total count of classrooms",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="count", type="integer", example=10)
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
     * @OA\Post(
     *     path="/api/classrooms",
     *     summary="Create a new classroom",
     *     tags={"Classrooms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom_du_local", "departement", "capacite"},
     *             @OA\Property(property="nom_du_local", type="string", example="Amphi A"),
     *             @OA\Property(property="departement", type="string", example="Informatique"),
     *             @OA\Property(property="capacite", type="integer", example=100),
     *             @OA\Property(property="liste_des_equipements", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Classroom created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/classrooms/{id}",
     *     summary="Get a specific classroom",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Classroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Classroom details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classroom not found"
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/classrooms/{id}",
     *     summary="Update a classroom",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Classroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom_du_local", type="string", example="Amphi A"),
     *             @OA\Property(property="departement", type="string", example="Informatique"),
     *             @OA\Property(property="capacite", type="integer", example=100),
     *             @OA\Property(property="liste_des_equipements", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Classroom updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/classrooms/{id}",
     *     summary="Delete a classroom",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Classroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Classroom deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classroom not found"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/classrooms/available",
     *     summary="Get all available classrooms",
     *     tags={"Classrooms"},
     *     @OA\Response(
     *         response=200,
     *         description="List of available classrooms"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/classrooms/schedule-exam",
     *     summary="Schedule an exam in a classroom",
     *     tags={"Classrooms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"classroom_id", "exam_id", "date_examen", "heure_debut", "heure_fin"},
     *             @OA\Property(property="classroom_id", type="integer", example=1),
     *             @OA\Property(property="exam_id", type="integer", example=1),
     *             @OA\Property(property="date_examen", type="string", format="date", example="2024-03-20"),
     *             @OA\Property(property="heure_debut", type="string", format="time", example="09:00"),
     *             @OA\Property(property="heure_fin", type="string", format="time", example="11:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Exam scheduled successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Classroom not available for the specified time slot"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/classrooms/name/{classroomName}",
     *     summary="Get a classroom by its name",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="classroomName",
     *         in="path",
     *         required=true,
     *         description="Name of the classroom",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Classroom details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classroom not found"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/classrooms/by-datetime",
     *     summary="Get classrooms by date, time range and department",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="date_examen",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2024-03-20")
     *     ),
     *     @OA\Parameter(
     *         name="heure_debut",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="time", example="09:00")
     *     ),
     *     @OA\Parameter(
     *         name="heure_fin",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="time", example="11:00")
     *     ),
     *     @OA\Parameter(
     *         name="departement",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", example="Informatique")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of scheduled classroom IDs",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="scheduled_classroom_ids",
     *                     type="array",
     *                     @OA\Items(type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="departement",
     *                     type="array",
     *                     @OA\Items(type="string", example="The departement field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
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
                'departement' => 'required|string|max:255'
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

            // Get scheduled classrooms with their details
            $scheduledClassrooms = DB::table('classroom_exam_schedule')
                ->join('classrooms', 'classroom_exam_schedule.classroom_id', '=', 'classrooms.id')
                ->where('date_examen', $request->date_examen)
                ->where('classrooms.departement', $request->departement)
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
                })
                ->select('classrooms.*', 'classroom_exam_schedule.date_examen', 'classroom_exam_schedule.heure_debut', 'classroom_exam_schedule.heure_fin')
                ->get();

            // Log the results
            \Illuminate\Support\Facades\Log::info('Search results:', [
                'count' => count($scheduledClassrooms),
                'classrooms' => $scheduledClassrooms
            ]);
            \Illuminate\Support\Facades\Log::info('=== CLASSROOM SEARCH END ===');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'scheduled_classrooms' => $scheduledClassrooms,
                    'count' => count($scheduledClassrooms)
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
     * @OA\Post(
     *     path="/api/classrooms/not-in-list",
     *     summary="Get classrooms not in the provided list",
     *     tags={"Classrooms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="classroom_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of classrooms not in the provided list"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/api/classrooms/available-count",
     *     summary="Get count of available classrooms for a time slot",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="date_examen",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="heure_debut",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="time")
     *     ),
     *     @OA\Parameter(
     *         name="heure_fin",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="time")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Count of available classrooms",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function availableCount(Request $request)
    {
        $date = $request->input('date_examen');
        $heureDebut = $request->input('heure_debut');
        $heureFin = $request->input('heure_fin');

        $sallesOccupees = ClassroomExamSchedule::where('date_examen', $date)
            ->where(function ($query) use ($heureDebut, $heureFin) {
                $query->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<=', $heureDebut)
                        ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<', $heureFin)
                        ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '>=', $heureDebut)
                        ->where('heure_fin', '<=', $heureFin);
                });
            })
            ->pluck('classroom_id');

        $sallesDisponibles = Classroom::whereNotIn('id', $sallesOccupees)->count();

        return response()->json(['count' => $sallesDisponibles]);
    }

    /**
     * @OA\Put(
     *     path="/api/classrooms/{id}/disponibilite",
     *     summary="Update classroom availability",
     *     tags={"Classrooms"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Classroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"disponible"},
     *             @OA\Property(property="disponible", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Classroom not found"
     *     )
     * )
     */
    public function updateDisponibilite(Request $request, $id)
    {
        $salle = Classroom::findOrFail($id);

        $validated = $request->validate([
            'disponible' => 'required|boolean'
        ]);

        $salle->update(['disponible' => $validated['disponible']]);

        return response()->json([
            'message' => 'Disponibilité mise à jour avec succès',
            'salle' => $salle
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/classrooms/amphitheaters",
     *     summary="Get all classrooms that start with 'Amphi' (case-insensitive)",
     *     tags={"Classrooms"},
     *     @OA\Response(
     *         response=200,
     *         description="List of amphitheaters",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom_du_local", type="string", example="Amphi A"),
     *                     @OA\Property(property="departement", type="string", example="Informatique"),
     *                     @OA\Property(property="capacite", type="integer", example=100),
     *                     @OA\Property(property="liste_des_equipements", type="array", @OA\Items(type="string"))
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
    public function getAmphitheaters()
    {
        try {
            $amphitheaters = Classroom::whereRaw('LOWER(nom_du_local) LIKE ?', ['amphi%'])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $amphitheaters
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve amphitheaters',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
