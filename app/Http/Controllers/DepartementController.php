<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Departements",
 *     description="API Endpoints for managing departments"
 * )
 */
class DepartementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/departements",
     *     summary="Get all departments",
     *     tags={"Departements"},
     *     @OA\Response(
     *         response=200,
     *         description="List of departments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id_departement", type="integer"),
     *                 @OA\Property(property="nom_departement", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $departements = Departement::all();
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

    /**
     * @OA\Get(
     *     path="/api/departements/{id}",
     *     summary="Get a department by ID",
     *     tags={"Departements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the department",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id_departement", type="integer"),
     *                 @OA\Property(property="nom_departement", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $departement = Departement::find($id);
            if (!$departement) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Departement not found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $departement
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve departement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
