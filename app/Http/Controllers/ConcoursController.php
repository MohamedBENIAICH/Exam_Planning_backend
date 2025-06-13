<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\Candidat;
use App\Services\ConcoursNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ConcoursController extends Controller
{
    protected $concoursNotificationService;

    public function __construct(ConcoursNotificationService $concoursNotificationService)
    {
        $this->concoursNotificationService = $concoursNotificationService;
    }

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
            'candidats' => 'nullable|array',
            'candidats.*.CNE' => 'required|string|max:255',
            'candidats.*.CIN' => 'required|string|max:255',
            'candidats.*.nom' => 'required|string|max:255',
            'candidats.*.prenom' => 'required|string|max:255',
            'candidats.*.email' => 'required|email|max:255',
            'superviseurs' => 'nullable|array',
            'superviseurs.*' => 'integer|exists:superviseurs,id',
            'professeurs' => 'nullable|array',
            'professeurs.*' => 'integer|exists:professeurs,id',
        ]);

        $concours = Concours::create($validated);

        // Process and attach candidats
        if (isset($validated['candidats'])) {
            $candidatIds = [];
            foreach ($validated['candidats'] as $candidatData) {
                $candidat = Candidat::firstOrCreate(
                    ['CNE' => $candidatData['CNE']],
                    [
                        'CIN' => $candidatData['CIN'],
                        'nom' => $candidatData['nom'],
                        'prenom' => $candidatData['prenom'],
                        'email' => $candidatData['email'],
                    ]
                );
                $candidatIds[] = $candidat->id;
            }
            $concours->candidats()->attach($candidatIds);
        }

        if (isset($validated['superviseurs'])) {
            $concours->superviseurs()->attach($validated['superviseurs']);
        }
        if (isset($validated['professeurs'])) {
            $concours->professeurs()->attach($validated['professeurs']);
        }

        $concours->load(['candidats', 'superviseurs', 'professeurs']);

        // Envoyer automatiquement les notifications de surveillance
        try {
            $this->concoursNotificationService->sendSurveillanceNotifications($concours);
            Log::info('Notifications de surveillance envoyées pour le concours', ['concours_id' => $concours->id]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de surveillance', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

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

    /**
     * Send convocations to all candidates of a concours
     */
    public function sendConvocations($id)
    {
        try {
            $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])->find($id);

            if (!$concours) {
                return response()->json(['message' => 'Concours not found'], 404);
            }

            if ($concours->candidats->count() === 0) {
                return response()->json(['message' => 'Aucun candidat assigné à ce concours'], 400);
            }

            // Envoyer les convocations
            $this->concoursNotificationService->generateAndSendNotifications($concours);

            return response()->json([
                'message' => 'Convocations envoyées avec succès',
                'candidats_count' => $concours->candidats->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des convocations', [
                'concours_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi des convocations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send surveillance notifications to supervisors and professors
     */
    public function sendSurveillanceNotifications($id)
    {
        try {
            $concours = Concours::with(['superviseurs', 'professeurs'])->find($id);

            if (!$concours) {
                return response()->json(['message' => 'Concours not found'], 404);
            }

            if ($concours->superviseurs->count() === 0 && $concours->professeurs->count() === 0) {
                return response()->json(['message' => 'Aucun superviseur ou professeur assigné à ce concours'], 400);
            }

            // Envoyer les notifications de surveillance
            $this->concoursNotificationService->sendSurveillanceNotifications($concours);

            return response()->json([
                'message' => 'Notifications de surveillance envoyées avec succès',
                'superviseurs_count' => $concours->superviseurs->count(),
                'professeurs_count' => $concours->professeurs->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de surveillance', [
                'concours_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'envoi des notifications de surveillance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a PDF report for a specific concours.
     */
    public function downloadReport($id)
    {
        try {
            $concours = Concours::with([
                'candidats',
                'superviseurs',
                'professeurs'
            ])->findOrFail($id);

            // Générer le PDF
            $pdf = Pdf::loadView('pdf.concours_report', compact('concours'));

            // Configurer le PDF (taille, orientation)
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            // Nom du fichier
            $filename = 'Compte_Rendu_Concours_' . str_replace(' ', '_', $concours->titre) . '_' . \Carbon\Carbon::parse($concours->date_concours)->format('Ymd') . '.pdf';

            // Télécharger le PDF
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating concours report PDF: ' . $e->getMessage(), [
                'concours_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate PDF report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
