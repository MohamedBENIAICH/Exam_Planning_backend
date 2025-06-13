<?php

namespace App\Http\Controllers;

use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="FST Digital API Documentation",
 *     description="API documentation for FST Digital Backend",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 */
class SuperviseurController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/superviseurs",
     *     summary="Get all superviseurs",
     *     tags={"Superviseurs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of superviseurs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="prenom", type="string"),
     *                 @OA\Property(property="poste", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        // $superviseurs = Superviseur::select('id', 'nom', 'prenom', 'poste')
        //     ->orderBy('nom')
        //     ->get();

        $superviseurs = Superviseur::get();

        return response()->json($superviseurs);
    }

    /**
     * @OA\Get(
     *     path="/api/superviseurs/by-service",
     *     summary="Get superviseurs by department",
     *     tags={"Superviseurs"},
     *     @OA\Parameter(
     *         name="service",
     *         in="query",
     *         required=true,
     *         description="Department name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of superviseurs in the department",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="nom", type="string"),
     *                     @OA\Property(property="prenom", type="string"),
     *                     @OA\Property(property="poste", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getByService(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'service' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get supervisors by department
            $superviseurs = Superviseur::where('service', $request->service)
                ->select('id', 'service', 'nom', 'prenom', 'type')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $superviseurs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve supervisors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/superviseurs/services",
     *     summary="Get all unique departments",
     *     tags={"Superviseurs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of unique departments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getAllServices()
    {
        try {
            // Get all unique departments
            $services = Superviseur::select('service')
                ->distinct()
                ->orderBy('service')
                ->pluck('service');

            return response()->json([
                'status' => 'success',
                'data' => $services
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedDate = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:superviseurs',
            'poste' => 'required|string',
            'service' => 'required|string'
        ]);

        $superviseur = Superviseur::create($validatedDate);

        return response()->json($superviseur, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/superviseurs/{id}",
     *     summary="Delete a supervisor by ID",
     *     tags={"Superviseurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of supervisor to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supervisor deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supervisor not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $superviseur = Superviseur::findOrFail($id);
        $superviseur->delete();

        return response()->json(['message' => 'Supervisor deleted successfully'], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/superviseurs/{id}",
     *     summary="Update a supervisor by ID",
     *     tags={"Superviseurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of supervisor to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom", type="string", description="Supervisor's last name"),
     *             @OA\Property(property="prenom", type="string", description="Supervisor's first name"),
     *             @OA\Property(property="email", type="string", format="email", description="Supervisor's email address"),
     *             @OA\Property(property="poste", type="string", description="Supervisor's position"),
     *             @OA\Property(property="service", type="string", description="Supervisor's service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supervisor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="poste", type="string"),
     *             @OA\Property(property="service", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supervisor not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $superviseur = Superviseur::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'nullable|string',
            'prenom' => 'nullable|string',
            'email' => 'nullable|email|unique:superviseurs,email,' . $id,
            'poste' => 'nullable|string',
            'service' => 'nullable|string'
        ]);

        $superviseur->update($validatedData);

        return response()->json($superviseur, 200);
    }
}
