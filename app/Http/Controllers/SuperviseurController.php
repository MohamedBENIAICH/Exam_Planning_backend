<?php

namespace App\Http\Controllers;

use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuperviseurController extends Controller
{
    /**
     * Get supervisors by department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
     * Get all unique departments from the supervisors table.
     *
     * @return \Illuminate\Http\JsonResponse
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
