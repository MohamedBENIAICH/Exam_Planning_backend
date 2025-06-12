<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;

class CandidatController extends Controller
{
    // List all candidats
    public function index()
    {
        return response()->json(Candidat::all());
    }

    // Store a new candidat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'CNE' => 'required|string|unique:candidats,CNE',
            'CIN' => 'required|string|unique:candidats,CIN',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:candidats,email',
        ]);

        $candidat = Candidat::create($validated);
        return response()->json($candidat, 201);
    }

    // Show a single candidat
    public function show($id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) {
            return response()->json(['message' => 'Candidat not found'], 404);
        }
        return response()->json($candidat);
    }

    // Update a candidat
    public function update(Request $request, $id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) {
            return response()->json(['message' => 'Candidat not found'], 404);
        }

        $validated = $request->validate([
            'CNE' => 'sometimes|required|string|unique:candidats,CNE,' . $id,
            'CIN' => 'sometimes|required|string|unique:candidats,CIN,' . $id,
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:candidats,email,' . $id,
        ]);

        $candidat->update($validated);
        return response()->json($candidat);
    }

    // Delete a candidat
    public function destroy($id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) {
            return response()->json(['message' => 'Candidat not found'], 404);
        }
        $candidat->delete();
        return response()->json(['message' => 'Candidat deleted successfully']);
    }
}
