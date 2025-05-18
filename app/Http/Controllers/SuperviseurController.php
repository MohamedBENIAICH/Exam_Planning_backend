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
        $superviseurs = Superviseur::select('id', 'nom', 'prenom', 'poste')
            ->orderBy('nom')
            ->get();

        return response()->json($superviseurs);
    }

    /**
     * @OA\Get(
     *     path="/api/superviseurs/by-departement",
     *     summary="Get superviseurs by department",
     *     tags={"Superviseurs"},
     *     @OA\Parameter(
     *         name="departement",
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
    public function getByDepartement(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'departement' => 'required|string'
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
            $superviseurs = Superviseur::where('departement', $request->departement)
                ->select('id', 'departement', 'nom', 'prenom', 'type')
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
     *     path="/api/superviseurs/departements",
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
    public function getAllDepartements()
    {
        try {
            // Get all unique departments
            $departements = Superviseur::select('departement')
                ->distinct()
                ->orderBy('departement')
                ->pluck('departement');

            return response()->json([
                'status' => 'success',
                'data' => $departements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
