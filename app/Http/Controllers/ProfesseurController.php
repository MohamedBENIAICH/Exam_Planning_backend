<?php

namespace App\Http\Controllers;

use App\Models\Professeur;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Professeurs",
 *     description="API Endpoints for managing professors"
 * )
 */
class ProfesseurController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/professeurs/by-departement",
     *     summary="Get professors by department",
     *     tags={"Professeurs"},
     *     @OA\Parameter(
     *         name="departement",
     *         in="query",
     *         required=true,
     *         description="Department name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of professors in the department",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="prenom", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="departement", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getByDepartement(Request $request)
    {
        $departement = $request->query('departement');

        $professeurs = Professeur::where('departement', $departement)
            ->select('id', 'nom', 'prenom', 'email', 'departement')
            ->get();

        return response()->json($professeurs);
    }

    /**
     * @OA\Get(
     *     path="/api/professeurs/departements",
     *     summary="Get all unique departments",
     *     tags={"Professeurs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of unique departments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="string")
     *         )
     *     )
     * )
     */
    public function getAllDepartements()
    {
        $departements = Professeur::select('departement')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');

        return response()->json($departements);
    }
}
