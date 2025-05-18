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
}
