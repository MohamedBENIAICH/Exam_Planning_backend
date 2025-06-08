<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Module;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormationController extends Controller
{
    /**
     * Display a listing of all formations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $formations = Formation::all();

            return response()->json([
                'status' => 'success',
                'data' => $formations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve formations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of filieres for a specific formation.
     *
     * @param int $id_formation
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilieresByFormation($id_formation)
    {
        try {
            $formation = Formation::findOrFail($id_formation);
            $filieres = $formation->filieres;

            return response()->json([
                'status' => 'success',
                'data' => $filieres
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Formation not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve filieres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of modules for a specific formation, filiere and semester.
     *
     * @param int $id_formation
     * @param int $id_filiere
     * @param string $semestre
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModulesByFormationAndSemester($id_formation, $id_filiere, $semestre)
    {
        try {
            $modules = Module::select('modules.*')
                ->join('filiere_module', 'modules.id_module', '=', 'filiere_module.id_module')
                ->join('filieres', 'filiere_module.id_filiere', '=', 'filieres.id_filiere')
                ->where('filieres.id_formation', $id_formation)
                ->where('filieres.id_filiere', $id_filiere)
                ->where('modules.semestre', $semestre)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $modules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve modules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get formation and filiere by their IDs.
     *
     * @param int $id_formation
     * @param int $id_filiere
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormationAndFiliere($id_formation, $id_filiere)
    {
        try {
            $formation = Formation::findOrFail($id_formation);
            $filiere = Filiere::where('id_filiere', $id_filiere)
                ->where('id_formation', $id_formation)
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'formation' => $formation,
                    'filiere' => $filiere
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Formation or Filiere not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve formation and filiere',
                'error' => $e->getMessage()
            ], 500);
        } 
       }   /**
             * Store a newly created formation.
             *
             * @param \Illuminate\Http\Request $request
             * @return \Illuminate\Http\JsonResponse
             */
            public function store(Request $request)
            {
                try {
                    $request->validate([
                        'formation_intitule' => 'required|string|max:255'
                    ]);
        
                    $formation = Formation::create($request->all());
        
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Formation created successfully',
                        'data' => $formation
                    ], 201);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $e->errors()
                    ], 422);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to create formation',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        
            /**
             * Update the specified formation.
             *
             * @param \Illuminate\Http\Request $request
             * @param int $id
             * @return \Illuminate\Http\JsonResponse
             */
            public function update(Request $request, $id)
            {
                try {
                    $formation = Formation::findOrFail($id);
        
                    $request->validate([
                        'formation_intitule' => 'required|string|max:255'
                    ]);

                    $formation->update($request->all());
        
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Formation updated successfully',
                        'data' => $formation
                    ]);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Formation not found'
                    ], 404);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation error',
                        'errors' => $e->errors()
                    ], 422);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to update formation',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        
            /**
             * Remove the specified formation.
             *
             * @param int $id
             * @return \Illuminate\Http\JsonResponse
             */
            public function delete($id)
            {
                try {
                    $formation = Formation::findOrFail($id);
                    $formation->delete();
        
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Formation deleted successfully'
                    ]);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Formation not found'
                    ], 404);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to delete formation',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            public function show($id)
    {
        try {
            $formation = Formation::find($id);
            if (!$formation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Formation not found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $formation
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve formation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}