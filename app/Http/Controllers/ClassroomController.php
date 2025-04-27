<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Get the number of available classrooms for scheduling.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableCount()
    {
        try {
            $count = Classroom::where('disponible_pour_planification', true)->count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count available classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
            'disponible_pour_planification' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $classroom = Classroom::create($request->all());
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
            'disponible_pour_planification' => 'boolean'
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
            $classrooms = Classroom::where('disponible_pour_planification', true)->get();

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

    public function updateDisponibilite(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'disponible_pour_planification' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->disponible_pour_planification = $request->input('disponible_pour_planification');
            $classroom->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Disponibilité mise à jour avec succès',
                'classroom' => $classroom
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la mise à jour de la disponibilité',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
