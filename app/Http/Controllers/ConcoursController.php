<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use Illuminate\Http\Request;

class ConcoursController extends Controller
{
    public function index()
    {
        // Return all concours with their relations
        return response()->json(
            Concours::with(['candidats', 'superviseurs', 'professeurs'])->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_concours' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'locaux' => 'nullable|string',
            'type_epreuve' => 'required|in:écrit,oral',
            'candidats' => 'array',
            'candidats.*' => 'integer|exists:candidats,id',
            'superviseurs' => 'array',
            'superviseurs.*' => 'integer|exists:superviseurs,id',
            'professeurs' => 'array',
            'professeurs.*' => 'integer|exists:professeurs,id',
        ]);

        $concours = Concours::create($validated);

        if (isset($validated['candidats'])) {
            $concours->candidats()->attach($validated['candidats']);
        }
        if (isset($validated['superviseurs'])) {
            $concours->superviseurs()->attach($validated['superviseurs']);
        }
        if (isset($validated['professeurs'])) {
            $concours->professeurs()->attach($validated['professeurs']);
        }

        $concours->load(['candidats', 'superviseurs', 'professeurs']);

        return response()->json($concours, 201);
    }

    public function show($id)
    {
        $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])->find($id);
        if (!$concours) {
            return response()->json(['message' => 'Concours not found'], 404);
        }
        return response()->json($concours);
    }

    public function update(Request $request, $id)
    {
        $concours = Concours::find($id);
        if (!$concours) {
            return response()->json(['message' => 'Concours not found'], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date_concours' => 'sometimes|required|date',
            'heure_debut' => 'sometimes|required',
            'heure_fin' => 'sometimes|required',
            'locaux' => 'nullable|string',
            'type_epreuve' => 'sometimes|required|in:écrit,oral',
            'candidats' => 'array',
            'candidats.*' => 'integer|exists:candidats,id',
            'superviseurs' => 'array',
            'superviseurs.*' => 'integer|exists:superviseurs,id',
            'professeurs' => 'array',
            'professeurs.*' => 'integer|exists:professeurs,id',
        ]);

        $concours->update($validated);

        if (isset($validated['candidats'])) {
            $concours->candidats()->sync($validated['candidats']);
        }
        if (isset($validated['superviseurs'])) {
            $concours->superviseurs()->sync($validated['superviseurs']);
        }
        if (isset($validated['professeurs'])) {
            $concours->professeurs()->sync($validated['professeurs']);
        }

        $concours->load(['candidats', 'superviseurs', 'professeurs']);

        return response()->json($concours);
    }

    public function destroy($id)
    {
        $concours = Concours::find($id);
        if (!$concours) {
            return response()->json(['message' => 'Concours not found'], 404);
        }
        $concours->delete();
        return response()->json(['message' => 'Concours deleted successfully']);
    }

    /**
     * Get the last 5 concours created.
     */
    public function latest()
    {
        $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($concours);
    }
}
