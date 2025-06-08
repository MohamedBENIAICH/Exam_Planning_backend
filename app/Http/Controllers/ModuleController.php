<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Modules",
 *     description="API Endpoints for managing modules"
 * )
 */
class ModuleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/modules/{id}/name",
     *     summary="Get module name by ID",
     *     tags={"Modules"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Module ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Module name",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="module_intitule", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Module not found"
     *     )
     * )
     */
    public function getModuleName($id)
    {
        try {
            $module = Module::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id_module' => $module->id_module,
                    'module_name' => $module->module_intitule
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Module not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Display a listing of the modules.
    public function index()
    {
        $modules = Module::with('filieres')->get();
        return response()->json($modules);
    }

    // Store a newly created module in storage and attach filieres
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_intitule' => 'required|string|max:255',
            'semestre' => 'required|string|max:50',
            'filieres' => 'required|array',
            'filieres.*' => 'integer|exists:filieres,id_filiere',
        ]);

        $module = Module::create([
            'module_intitule' => $validated['module_intitule'],
            'semestre' => $validated['semestre'],
        ]);

        // Attach filieres to the module
        $module->filieres()->attach($validated['filieres']);

        // Load filieres relationship for response
        $module->load('filieres');

        return response()->json($module, 201);
    }

    // Display the specified module.
    public function show($id)
    {
        $module = Module::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }
        return response()->json($module);
    }

    // Update the specified module in storage.
    public function update(Request $request, $id)
    {
        $module = Module::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }
        $validated = $request->validate([
            'module_intitule' => 'sometimes|required|string|max:255',
            'semestre' => 'sometimes|required|string|max:50',
        ]);
        $module->update($validated);
        return response()->json($module);
    }

    // Remove the specified module from storage.
    public function destroy($id)
    {
        $module = Module::find($id);
        if (!$module) {
            return response()->json(['message' => 'Module not found'], 404);
        }
        $module->delete();
        return response()->json(['message' => 'Module deleted successfully']);
    }

    // Retrieve modules with filieres and each filiere's formation name
    public function modulesWithFilieresAndFormationName()
    {
        $modules = Module::with(['filieres.formation'])->get();

        // Transform the data to include formation name in each filiere
        $result = $modules->map(function ($module) {
            $filieres = $module->filieres->map(function ($filiere) {
                return [
                    'id_filiere' => $filiere->id_filiere,
                    'filiere_intitule' => $filiere->filiere_intitule,
                    'id_departement' => $filiere->id_departement,
                    'id_formation' => $filiere->id_formation,
                    'formation_name' => $filiere->formation ? $filiere->formation->formation_intitule : null,
                    'created_at' => $filiere->created_at,
                    'updated_at' => $filiere->updated_at,
                ];
            });

            return [
                'id_module' => $module->id_module,
                'module_intitule' => $module->module_intitule,
                'semestre' => $module->semestre,
                'created_at' => $module->created_at,
                'updated_at' => $module->updated_at,
                'filieres' => $filieres,
            ];
        });
        return response()->json($result);
    }
}
