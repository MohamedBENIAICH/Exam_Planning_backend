<?php

namespace App\Http\Controllers;

use App\Services\ProfesseurService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Professeurs",
 *     description="API Endpoints for managing professors"
 * )
 */
class ProfesseurController extends Controller
{
    protected $professeurService;

    public function __construct(ProfesseurService $professeurService)
    {
        $this->professeurService = $professeurService;
    }
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
        $professeurs = $this->professeurService->getProfesseursByDepartement($departement);
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
        $departements = $this->professeurService->getAllDepartements();
        return response()->json($departements);
    }

    /**
     * @OA\Get(
     *     path="/professeurs",
     *     summary="Get all professors",
     *     tags={"Professeurs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all professors",
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
    public function index()
    {
        $professeurs = $this->professeurService->getAllProfesseurs();
        return response()->json($professeurs);
    }

    /**
     * @OA\Post(
     *     path="/api/professeurs",
     *     summary="Create a new professor",
     *     tags={"Professeurs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "nom", "prenom", "departement"},
     *             @OA\Property(property="email", type="string", format="email", description="Professor's email address"),
     *             @OA\Property(property="nom", type="string", description="Professor's last name"),
     *             @OA\Property(property="prenom", type="string", description="Professor's first name"),
     *             @OA\Property(property="departement", type="string", description="Professor's department")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Professor created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="departement", type="string")
     *         )
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

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:professeurs',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'departement' => 'required|string'
        ]);

        $professeur = $this->professeurService->createProfesseur($validatedData);

        return response()->json($professeur, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/professeurs/{id}",
     *     summary="Delete a professor by ID",
     *     tags={"Professeurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of professor to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Professor deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Professor not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->professeurService->deleteProfesseur($id);
        return response()->json(['message' => 'Professor deleted successfully'], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/professeurs/{id}",
     *     summary="Update a professor by ID",
     *     tags={"Professeurs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of professor to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="Professor's email address"),
     *             @OA\Property(property="nom", type="string", description="Professor's last name"),
     *             @OA\Property(property="prenom", type="string", description="Professor's first name"),
     *             @OA\Property(property="departement", type="string", description="Professor's department")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Professor updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="departement", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Professor not found"
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
        $validatedData = $request->validate([
            'email' => 'nullable|email|unique:professeurs,email,' . $id,
            'nom' => 'nullable|string',
            'prenom' => 'nullable|string',
            'departement' => 'nullable|string'
        ]);

        $professeur = $this->professeurService->updateProfesseur($id, $validatedData);

        return response()->json($professeur, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/professeurs/count",
     *     summary="Get total number of professors",
     *     tags={"Professeurs"},
     *     @OA\Response(
     *         response=200,
     *         description="Total number of professors",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function count()
    {
        $count = $this->professeurService->getProfesseurCount();
        return response()->json(['status' => 'success', 'count' => $count]);
    }
}
