<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the classrooms.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $classrooms = Classroom::all();

            return response()->json([
                'status' => 'success',
                'data' => $classrooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the total number of classrooms in the database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        try {
            $count = Classroom::count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the number of available classrooms for scheduling.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableCount()
    {
        try {
            $count = Classroom::where('disponible_pour_planification', true)->count();

            return response()->json([
                'status' => 'success',
                'count' => $count
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count available classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
