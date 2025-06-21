<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\Classroom;
use App\Models\Candidat;
use App\Models\ConcoursClassroomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConcoursClassroomAssignmentController extends Controller
{
    /**
     * Create or update assignments for a concours.
     */
    public function store(Request $request, $concours_id)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'classroom_ids' => 'required|array',
                'classroom_ids.*' => 'exists:classrooms,id',
                'candidat_ids' => 'required|array',
                'candidat_ids.*' => 'exists:candidats,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get concours and validate it exists
            $concours = Concours::findOrFail($concours_id);

            // Get classrooms and calculate total capacity
            $classrooms = Classroom::whereIn('id', $request->classroom_ids)
                ->orderBy('capacite', 'desc')
                ->get();
                
            $totalCapacity = $classrooms->sum('capacite');

            // Check if we have enough capacity
            if ($totalCapacity < count($request->candidat_ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient classroom capacity',
                    'details' => [
                        'total_candidats' => count($request->candidat_ids),
                        'total_capacity' => $totalCapacity,
                        'missing_capacity' => count($request->candidat_ids) - $totalCapacity
                    ]
                ], 400);
            }

            // Get all candidats at once
            $candidats = Candidat::whereIn('id', $request->candidat_ids)->get()->keyBy('id');
            
            // Validate all candidat IDs exist
            $invalidCandidatIds = array_diff($request->candidat_ids, $candidats->pluck('id')->toArray());
            if (!empty($invalidCandidatIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Some candidats were not found',
                    'invalid_candidat_ids' => array_values($invalidCandidatIds)
                ], 404);
            }

            // Start transaction
            DB::beginTransaction();

            try {
                // Delete existing assignments for this concours
                ConcoursClassroomAssignment::where('concours_id', $concours_id)->delete();

                // Assign candidats to classrooms
                $assignments = [];
                $currentClassroomIndex = 0;
                $currentSeatNumber = 1;
                $assignedCandidats = [];

                // Process each candidat in the order they were provided
                foreach ($request->candidat_ids as $candidatId) {
                    // Skip if candidat was already assigned (shouldn't happen with validation)
                    if (in_array($candidatId, $assignedCandidats)) {
                        continue;
                    }

                    // Get the current classroom
                    $classroom = $classrooms[$currentClassroomIndex];

                    // Create assignment
                    $assignment = ConcoursClassroomAssignment::create([
                        'concours_id' => $concours_id,
                        'classroom_id' => $classroom->id,
                        'candidat_id' => $candidatId,
                        'seat_number' => $currentSeatNumber
                    ]);

                    $assignments[] = $assignment;
                    $assignedCandidats[] = $candidatId;

                    // Move to next seat
                    $currentSeatNumber++;

                    // If classroom is full, move to next classroom
                    if ($currentSeatNumber > $classroom->capacite && $currentClassroomIndex < count($classrooms) - 1) {
                        $currentClassroomIndex++;
                        $currentSeatNumber = 1;
                    }
                }


                DB::commit();

                // Prepare response
                $response = [
                    'status' => 'success',
                    'message' => 'Candidats assigned to classrooms successfully',
                    'data' => [
                        'concours_id' => $concours_id,
                        'total_candidats' => count($request->candidat_ids),
                        'total_capacity' => $totalCapacity,
                        'classrooms_used' => $currentClassroomIndex + 1,
                        'assignments' => []
                    ]
                ];

                // Group assignments by classroom for the response
                foreach ($classrooms as $classroom) {
                    $classroomAssignments = ConcoursClassroomAssignment::with('candidat')
                        ->where('concours_id', $concours_id)
                        ->where('classroom_id', $classroom->id)
                        ->orderBy('seat_number')
                        ->get();

                    if ($classroomAssignments->isEmpty()) {
                        continue; // Skip empty classrooms
                    }

                    $response['data']['assignments'][] = [
                        'classroom_id' => $classroom->id,
                        'classroom_name' => $classroom->nom_du_local,
                        'capacity' => $classroom->capacite,
                        'assigned' => $classroomAssignments->count(),
                        'available' => $classroom->capacite - $classroomAssignments->count(),
                        'candidats' => $classroomAssignments->map(function ($assignment) {
                            return [
                                'candidat_id' => $assignment->candidat->id,
                                'cne' => $assignment->candidat->CNE,
                                'cin' => $assignment->candidat->CIN,
                                'nom' => $assignment->candidat->nom,
                                'prenom' => $assignment->candidat->prenom,
                                'email' => $assignment->candidat->email,
                                'seat_number' => $assignment->seat_number
                            ];
                        })->values()
                    ];
                }

                return response()->json($response, 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e; // Re-throw to be caught by the outer catch
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create classroom assignments',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }

    /**
     * Get assignments for a concours.
     */
    public function show($concours_id)
    {
        try {
            $concours = Concours::findOrFail($concours_id);

            $assignments = ConcoursClassroomAssignment::with(['classroom', 'candidat'])
                ->where('concours_id', $concours_id)
                ->get()
                ->groupBy('classroom_id');

            $response = [
                'status' => 'success',
                'data' => [
                    'concours_id' => $concours_id,
                    'concours_titre' => $concours->titre,
                    'date_concours' => $concours->date_concours,
                    'assignments' => []
                ]
            ];

            foreach ($assignments as $classroomId => $classroomAssignments) {
                $classroom = $classroomAssignments->first()->classroom;

                $response['data']['assignments'][] = [
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->nom_du_local,
                    'departement' => $classroom->departement,
                    'capacity' => $classroom->capacite,
                    'assigned' => $classroomAssignments->count(),
                    'available' => $classroom->capacite - $classroomAssignments->count(),
                    'candidats' => $classroomAssignments->map(function ($assignment) {
                        return [
                            'candidat_id' => $assignment->candidat->id,
                            'cne' => $assignment->candidat->CNE,
                            'cin' => $assignment->candidat->CIN,
                            'nom' => $assignment->candidat->nom,
                            'prenom' => $assignment->candidat->prenom,
                            'email' => $assignment->candidat->email,
                            'seat_number' => $assignment->seat_number
                        ];
                    })->sortBy('seat_number')->values()
                ];
            }

            // Sort classrooms by name for consistent output
            if (isset($response['data']['assignments'])) {
                usort($response['data']['assignments'], function($a, $b) {
                    return strcmp($a['classroom_name'], $b['classroom_name']);
                });
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve concours assignments',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }

    /**
     * Delete all assignments for a concours.
     */
    public function destroy($concours_id)
    {
        try {
            $concours = Concours::findOrFail($concours_id);

            $deleted = ConcoursClassroomAssignment::where('concours_id', $concours_id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Concours classroom assignments deleted successfully',
                'data' => [
                    'concours_id' => $concours_id,
                    'deleted_assignments' => $deleted
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete concours assignments',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }
    
    /**
     * Get available classrooms for a concours based on date and time.
     */
    public function getAvailableClassrooms(Request $request, $concours_id)
    {
        try {
            $concours = Concours::findOrFail($concours_id);
            
            // Get all classrooms that don't have any exam scheduled at the same time
            $availableClassrooms = Classroom::whereDoesntHave('exams', function($query) use ($concours) {
                $query->where('date_examen', $concours->date_concours)
                      ->where(function($q) use ($concours) {
                          $q->whereBetween('heure_debut', [$concours->heure_debut, $concours->heure_fin])
                            ->orWhereBetween('heure_fin', [$concours->heure_debut, $concours->heure_fin])
                            ->orWhere(function($q2) use ($concours) {
                                $q2->where('heure_debut', '<=', $concours->heure_debut)
                                   ->where('heure_fin', '>=', $concours->heure_fin);
                            });
                      });
            })->get();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'concours_id' => $concours_id,
                    'date_concours' => $concours->date_concours,
                    'heure_debut' => $concours->heure_debut,
                    'heure_fin' => $concours->heure_fin,
                    'available_classrooms' => $availableClassrooms->map(function($classroom) {
                        return [
                            'id' => $classroom->id,
                            'nom_du_local' => $classroom->nom_du_local,
                            'departement' => $classroom->departement,
                            'capacite' => $classroom->capacite,
                            'liste_des_equipements' => $classroom->liste_des_equipements
                        ];
                    })
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get available classrooms',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : []
            ], 500);
        }
    }
}
