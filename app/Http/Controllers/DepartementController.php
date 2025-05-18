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
}
