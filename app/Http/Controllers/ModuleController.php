<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Get module name by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
