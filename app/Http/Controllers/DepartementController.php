<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartementController extends Controller
{
    /**
     * Display a listing of all departments.
     *
     * @return JsonResponse
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
