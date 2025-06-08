<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return response()->json(Filiere::all());
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'filiere_intitule' => 'required|string|max:255',
            'id_departement' => 'required|integer',
            'id_formation' => 'required|integer',
        ]);
        $filiere = Filiere::create($validated);
        return response()->json($filiere, 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filiere not found'], 404);
        }
        return response()->json($filiere);
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filiere not found'], 404);
        }
        $validated = $request->validate([
            'filiere_intitule' => 'sometimes|required|string|max:255',
            'id_departement' => 'sometimes|required|integer',
            'id_formation' => 'sometimes|required|integer',
        ]);
        $filiere->update($validated);
        return response()->json($filiere);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'Filiere not found'], 404);
        }
        $filiere->delete();
        return response()->json(['message' => 'Filiere deleted successfully']);
    }

    // Retrieve all filieres with their department names and filiere names
    public function filieresWithDepartementAndFormationNames()
    {
        $filieres = Filiere::join('departements', 'filieres.id_departement', '=', 'departements.id_departement')
            ->join('formations', 'filieres.id_formation', '=', 'formations.id_formation')
            ->select(
                'filieres.id_filiere as filiere_id',
                'filieres.id_departement',
                'filieres.id_formation',
                'filieres.filiere_intitule as filiere_name',
                'departements.nom_departement as departement_name',
                'formations.formation_intitule as formation_name'
            )
            ->get();

        return response()->json($filieres);
    }
}
