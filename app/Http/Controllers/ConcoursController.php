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

        // Vérifier la disponibilité des salles
        if ($validated['locaux']) {
            $locaux = explode(',', $validated['locaux']);
            $conflicts = $this->checkSalleAvailability($validated['date_concours'], $validated['heure_debut'], $validated['heure_fin'], $locaux);

            if (!empty($conflicts)) {
                return response()->json([
                    'message' => 'Conflit de disponibilité des salles',
                    'conflicts' => $conflicts
                ], 409);
            }
        }

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
            'candidats' => 'nullable|array',
            'candidats.*.CNE' => 'required_with:candidats|string|max:255',
            'candidats.*.CIN' => 'required_with:candidats|string|max:255',
            'candidats.*.nom' => 'required_with:candidats|string|max:255',
            'candidats.*.prenom' => 'required_with:candidats|string|max:255',
            'candidats.*.email' => 'required_with:candidats|email|max:255',
            'superviseurs' => 'nullable|array',
            'superviseurs.*' => 'integer|exists:superviseurs,id',
            'professeurs' => 'nullable|array',
            'professeurs.*' => 'integer|exists:professeurs,id',
        ]);

        // Vérifier la disponibilité des salles (en excluant le concours actuel)
        if (isset($validated['locaux']) && $validated['locaux']) {
            $locaux = explode(',', $validated['locaux']);
            $conflicts = $this->checkSalleAvailability(
                $validated['date_concours'] ?? $concours->date_concours,
                $validated['heure_debut'] ?? $concours->heure_debut,
                $validated['heure_fin'] ?? $concours->heure_fin,
                $locaux,
                $id // Exclure le concours actuel
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'message' => 'Conflit de disponibilité des salles',
                    'conflicts' => $conflicts
                ], 409);
            }
        }

        // Remove candidats from validated data before updating the concours
        $candidatsData = $validated['candidats'] ?? null;
        unset($validated['candidats']);

        $concours->update($validated);

        // Process and attach candidats
        if (isset($candidatsData)) {
            $candidatIds = [];
            foreach ($candidatsData as $candidatData) {
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
            $concours->candidats()->sync($candidatIds);
        }

        if (isset($validated['superviseurs'])) {
            $concours->superviseurs()->sync($validated['superviseurs']);
        }
        if (isset($validated['professeurs'])) {
            $concours->professeurs()->sync($validated['professeurs']);
        }

        $concours->load(['candidats', 'superviseurs', 'professeurs']);

        // Envoyer automatiquement les notifications de surveillance lors de la modification
        try {
            $this->concoursNotificationService->sendSurveillanceNotifications($concours);
            Log::info('Notifications de surveillance envoyées pour la modification du concours', ['concours_id' => $concours->id]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de surveillance lors de la modification', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

        // Send update notifications to professors and supervisors
        try {
            $this->concoursNotificationService->sendUpdateNotifications($concours);
            Log::info('Notifications de mise à jour envoyées pour la modification du concours', ['concours_id' => $concours->id]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de mise à jour lors de la modification', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

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
     * Cancel a concours instead of deleting it
     * This will send cancellation notifications to all concerned parties
     */
    public function cancel($id)
    {
        try {
            $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])->find($id);
            if (!$concours) {
                return response()->json(['message' => 'Concours not found'], 404);
            }

            // Send cancellation notifications before deleting
            app(\App\Services\ConcoursNotificationService::class)->sendCancellationNotifications($concours);

            // Delete the concours completely from database
            $concours->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Concours cancelled and deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send updated convocations to candidates after concours update
     */
    public function sendUpdatedConvocations($id)
    {
        try {
            $concours = Concours::with(['candidats'])->find($id);
            if (!$concours) {
                return response()->json(['message' => 'Concours not found'], 404);
            }

            // Send updated convocations
            app(\App\Services\ConcoursNotificationService::class)->sendUpdatedConvocations($concours);

            return response()->json([
                'status' => 'success',
                'message' => 'Updated convocations sent successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send updated convocations',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Vérifier la disponibilité des salles pour une date et heure données
     */
    private function checkSalleAvailability($date, $heureDebut, $heureFin, $locaux, $excludeConcoursId = null)
    {
        $conflicts = [];

        foreach ($locaux as $local) {
            $local = trim($local);

            // Vérifier les conflits avec les concours
            $query = Concours::where('date_concours', $date)
                ->where('locaux', 'LIKE', '%' . $local . '%')
                ->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where(function ($subQ) use ($heureDebut, $heureFin) {
                        // Le début du nouveau concours est dans la plage d'un concours existant
                        $subQ->where('heure_debut', '<=', $heureDebut)
                            ->where('heure_fin', '>', $heureDebut);
                    })->orWhere(function ($subQ) use ($heureDebut, $heureFin) {
                        // La fin du nouveau concours est dans la plage d'un concours existant
                        $subQ->where('heure_debut', '<', $heureFin)
                            ->where('heure_fin', '>=', $heureFin);
                    })->orWhere(function ($subQ) use ($heureDebut, $heureFin) {
                        // Le nouveau concours englobe complètement un concours existant
                        $subQ->where('heure_debut', '>=', $heureDebut)
                            ->where('heure_fin', '<=', $heureFin);
                    });
                });

            // Exclure le concours actuel lors de la modification
            if ($excludeConcoursId) {
                $query->where('id', '!=', $excludeConcoursId);
            }

            $conflictingConcours = $query->get();

            if ($conflictingConcours->count() > 0) {
                foreach ($conflictingConcours as $conflict) {
                    $conflicts[] = [
                        'local' => $local,
                        'type' => 'concours',
                        'conflict_with' => $conflict->titre,
                        'date' => $conflict->date_concours,
                        'heure_debut' => $conflict->heure_debut,
                        'heure_fin' => $conflict->heure_fin
                    ];
                }
            }

            // Vérifier les conflits avec les examens (si vous avez une table exams)
            // Vous pouvez ajouter ici la logique pour vérifier les conflits avec les examens
            // en utilisant la même logique que pour les concours
        }

        return $conflicts;
    }

    /**
     * API endpoint pour vérifier la disponibilité des salles
     */
    public function checkSalleAvailabilityAPI(Request $request)
    {
        $validated = $request->validate([
            'date_concours' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'locaux' => 'required|array',
            'locaux.*' => 'string',
            'exclude_concours_id' => 'nullable|integer'
        ]);

        $conflicts = $this->checkSalleAvailability(
            $validated['date_concours'],
            $validated['heure_debut'],
            $validated['heure_fin'],
            $validated['locaux'],
            $validated['exclude_concours_id'] ?? null
        );

        return response()->json([
            'available' => empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }
}
