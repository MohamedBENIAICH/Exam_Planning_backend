<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use App\Models\Candidat;
use App\Models\ConcoursClassroomAssignment;
use App\Services\ConcoursNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        // Log the raw request data
        \Illuminate\Support\Facades\Log::info('Raw request data:', $request->all());

        // Start database transaction
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // First, validate basic fields
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_concours' => 'required|date',
                'heure_debut' => 'required|date_format:H:i',
                'heure_fin' => 'required|date_format:H:i|after:heure_debut',
                'type_epreuve' => 'required|string|in:écrit,oral,pratique',
                'locaux' => 'required', // We'll validate this manually
                'candidats' => 'required', // We'll validate this manually
                'superviseurs' => 'required|array|min:1',
                'superviseurs.*' => 'exists:superviseurs,id',
                'professeurs' => 'required|array|min:1',
                'professeurs.*' => 'exists:professeurs,id',
            ]);

            // Process locaux
            $locaux = $validated['locaux'];
            if (is_string($locaux)) {
                $locaux = json_decode($locaux, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON in locaux field: ' . json_last_error_msg());
                }
            }

            if (!is_array($locaux) || empty($locaux)) {
                throw new \Exception('locaux must be a non-empty array');
            }

            // Validate each local
            foreach ($locaux as $local) {
                if (!is_array($local) || !isset($local['nom_local']) || !isset($local['capacity'])) {
                    throw new \Exception('Each local must have nom_local and capacity fields');
                }
            }

            // Process candidats
            $candidats = $validated['candidats'];
            if (is_string($candidats)) {
                $candidats = json_decode($candidats, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON in candidats field: ' . json_last_error_msg());
                }
            }

            if (!is_array($candidats) || empty($candidats)) {
                throw new \Exception('candidats must be a non-empty array');
            }

            // Check room availability
            $locauxNames = array_column($locaux, 'nom_local');
            $conflicts = $this->checkSalleAvailability(
                $validated['date_concours'],
                $validated['heure_debut'],
                $validated['heure_fin'],
                $locauxNames
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Salle non disponible',
                    'conflicts' => $conflicts
                ], 409);
            }

            // Store locaux as JSON
            $validated['locaux'] = json_encode($locaux);

            // Create concours
            $concours = Concours::create($validated);

            // Process classrooms
            $classrooms = [];
            foreach ($locaux as $local) {
                // First, find or create the classroom
                $classroom = \App\Models\Classroom::firstOrCreate(
                    ['nom_du_local' => $local['nom_local']],
                    [
                        'nom_du_local' => $local['nom_local'],
                        'capacite' => (int)$local['capacity'],
                        'departement' => 'Concours',
                        'liste_des_equipements' => json_encode(['tables', 'chaises'])
                    ]
                );

                // Update capacity if it's different
                if ($classroom->capacite != (int)$local['capacity']) {
                    $classroom->update(['capacite' => (int)$local['capacity']]);
                }

                $classrooms[] = $classroom;
            }

            // Process and attach candidats
            $candidatIds = [];
            $candidatModels = [];

            foreach ($candidats as $candidatData) {
                try {
                    // First try to find by CNE or CIN
                    $candidat = Candidat::where('CNE', $candidatData['CNE'])
                        ->orWhere('CIN', $candidatData['CIN'])
                        ->first();

                    // If not found, create a new one
                    if (!$candidat) {
                        $candidat = Candidat::create([
                            'CNE' => $candidatData['CNE'],
                            'CIN' => $candidatData['CIN'],
                            'nom' => $candidatData['nom'],
                            'prenom' => $candidatData['prenom'],
                            'email' => $candidatData['email']
                        ]);
                    }

                    $candidatIds[] = $candidat->id;
                    $candidatModels[] = $candidat;
                } catch (\Exception $e) {
                    // If there's an error (like duplicate CIN), try to find the existing candidate
                    $candidat = Candidat::where('CIN', $candidatData['CIN'])->first();
                    if ($candidat) {
                        $candidatIds[] = $candidat->id;
                        $candidatModels[] = $candidat;
                    } else {
                        throw $e; // Re-throw if we can't handle it
                    }
                }
            }

            // Attach candidates to concours
            $concours->candidats()->sync($candidatIds);

            // Attach supervisors and professors
            $concours->superviseurs()->sync($validated['superviseurs']);
            $concours->professeurs()->sync($validated['professeurs']);

            // Distribute candidates to classrooms
            $this->distributeCandidates($concours, $candidatModels, $classrooms);

            // Commit the transaction
            \Illuminate\Support\Facades\DB::commit();

            // Send notifications
            try {
                $this->concoursNotificationService->sendConcoursCreatedNotifications($concours);
            } catch (\Exception $e) {
                // Log the error but don't fail the request
                \Illuminate\Support\Facades\Log::error('Failed to send notifications: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Concours created successfully',
                'data' => $concours->load(['candidats', 'superviseurs', 'professeurs'])
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error creating concours: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribute candidates to classrooms for an exam
     *
     * @param \App\Models\Concours $concours
     * @param array $candidates
     * @param array $classrooms
     * @return void
     */
    protected function distributeCandidates($concours, $candidates, $classrooms)
    {
        $currentClassroomIndex = 0;
        $seatNumber = 1;

        // First, delete any existing assignments for this concours to avoid duplicates
        // but only if we have candidates to assign
        if (count($candidates) > 0) {
            \App\Models\ConcoursClassroomAssignment::where('concours_id', $concours->id)->delete();
        }

        // Track assigned candidate IDs to prevent duplicates in this distribution
        $assignedCandidateIds = [];

        foreach ($candidates as $candidate) {
            // Skip if this candidate is already assigned in this distribution
            if (in_array($candidate->id, $assignedCandidateIds)) {
                continue;
            }

            // If we've gone through all classrooms, log a warning and continue with the first classroom
            if ($currentClassroomIndex >= count($classrooms)) {
                \Illuminate\Support\Facades\Log::warning('Not enough classrooms for all candidates in concours', [
                    'concours_id' => $concours->id,
                    'candidate_count' => count($candidates),
                    'classroom_count' => count($classrooms)
                ]);
                $currentClassroomIndex = 0;
                $seatNumber = 1;
            }

            $classroom = $classrooms[$currentClassroomIndex];

            try {
                // First check if this candidate is already assigned to this concours
                $existingAssignment = \App\Models\ConcoursClassroomAssignment::where('concours_id', $concours->id)
                    ->where('candidat_id', $candidate->id)
                    ->first();

                if ($existingAssignment) {
                    // Update existing assignment
                    $existingAssignment->update([
                        'classroom_id' => $classroom->id,
                        'seat_number' => $seatNumber,
                        'status' => 'scheduled'
                    ]);
                } else {
                    // Create new assignment
                    $assignment = new \App\Models\ConcoursClassroomAssignment([
                        'concours_id' => $concours->id,
                        'classroom_id' => $classroom->id,
                        'candidat_id' => $candidate->id,
                        'seat_number' => $seatNumber,
                        'status' => 'scheduled'
                    ]);
                    $assignment->save();
                }

                // Add to assigned candidates for this distribution
                $assignedCandidateIds[] = $candidate->id;

                // Move to next seat
                $seatNumber++;

                // If classroom is full, move to next classroom
                if ($seatNumber > $classroom->capacite) {
                    $currentClassroomIndex++;
                    $seatNumber = 1;
                }
            } catch (\Exception $e) {
                // Log the error and continue with the next candidate
                \Illuminate\Support\Facades\Log::error('Error assigning candidate to classroom: ' . $e->getMessage(), [
                    'concours_id' => $concours->id,
                    'candidat_id' => $candidate->id,
                    'classroom_id' => $classroom->id,
                    'seat_number' => $seatNumber,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function show($id)
    {
        $concours = Concours::with([
            'candidats',
            'superviseurs',
            'professeurs'
        ])->find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours not found'], 404);
        }

        // Charger manuellement les affectations avec les détails
        $assignments = ConcoursClassroomAssignment::where('concours_id', $id)
            ->with(['candidat', 'classroom'])
            ->get();

        $concours->classroom_assignments = $assignments;

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

            // Robust distribution of candidats across locaux and save assignments (store method)
            $locauxToUse = [];
            if (isset($validated['locaux']) && $validated['locaux']) {
                $locauxToUse = is_array($validated['locaux']) ? $validated['locaux'] : explode(',', $validated['locaux']);
            } elseif ($concours->locaux) {
                $locauxToUse = explode(',', $concours->locaux);
            }

            if (!empty($candidatIds) && !empty($locauxToUse)) {
                \App\Models\ConcoursClassroomAssignment::where('concours_id', $concours->id)->delete();
                $chunks = array_chunk($candidatIds, ceil(count($candidatIds) / count($locauxToUse)));
                foreach ($chunks as $i => $chunk) {
                    $local = $locauxToUse[$i];
                    foreach ($chunk as $candidatId) {
                        \App\Models\ConcoursClassroomAssignment::create([
                            'concours_id' => $concours->id,
                            'candidat_id' => $candidatId,
                            'classroom_id' => \App\Models\Classroom::where('nom_du_local', $local)->first()->id,
                        ]);
                    }
                }
                \Illuminate\Support\Facades\Log::info('Candidats distributed to locaux (store)', ['concours_id' => $concours->id, 'locaux' => $locauxToUse, 'candidats' => $candidatIds]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Distribution skipped in store: missing candidats or locaux', ['concours_id' => $concours->id, 'locaux' => $locauxToUse, 'candidats' => $candidatIds ?? []]);
            }

            \Illuminate\Support\Facades\DB::commit();
            // Robust distribution of candidats across locaux and save assignments
            $locauxToUse = [];
            if (isset($validated['locaux']) && $validated['locaux']) {
                $locauxToUse = is_array($validated['locaux']) ? $validated['locaux'] : explode(',', $validated['locaux']);
            } elseif ($concours->locaux) {
                $locauxToUse = explode(',', $concours->locaux);
            }

            if (!empty($candidatIds) && !empty($locauxToUse)) {
                \App\Models\ConcoursClassroomAssignment::where('concours_id', $concours->id)->delete();
                $chunks = array_chunk($candidatIds, ceil(count($candidatIds) / count($locauxToUse)));
                foreach ($chunks as $i => $chunk) {
                    $local = $locauxToUse[$i];
                    foreach ($chunk as $candidatId) {
                        \App\Models\ConcoursClassroomAssignment::create([
                            'concours_id' => $concours->id,
                            'candidat_id' => $candidatId,
                            'classroom_id' => \App\Models\Classroom::where('nom_du_local', $local)->first()->id,
                        ]);
                    }
                }
                \Illuminate\Support\Facades\Log::info('Candidats distributed to locaux', ['concours_id' => $concours->id, 'locaux' => $locauxToUse, 'candidats' => $candidatIds]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Distribution skipped: missing candidats or locaux', ['concours_id' => $concours->id, 'locaux' => $locauxToUse, 'candidats' => $candidatIds ?? []]);
            }
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
            \Illuminate\Support\Facades\Log::info('Notifications de surveillance envoyées pour la modification du concours', ['concours_id' => $concours->id]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des notifications de surveillance lors de la modification', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

        // Send update notifications to professors and supervisors
        try {
            $this->concoursNotificationService->sendUpdateNotifications($concours);
            \Illuminate\Support\Facades\Log::info('Notifications de mise à jour envoyées pour la modification du concours', ['concours_id' => $concours->id]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des notifications de mise à jour lors de la modification', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

        // Envoyer automatiquement les convocations mises à jour avec QR codes aux candidats
        try {
            $this->concoursNotificationService->sendUpdatedConvocations($concours);
            \Illuminate\Support\Facades\Log::info('Convocations mises à jour avec QR codes envoyées pour la modification du concours', [
                'concours_id' => $concours->id,
                'candidats_count' => $concours->candidats->count()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des convocations mises à jour lors de la modification', [
                'concours_id' => $concours->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json($concours);
    }

    public function destroy($id)
    {
        try {
            $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])->find($id);

            if (!$concours) {
                return response()->json(['message' => 'Concours not found'], 404);
            }

            // Mettre à jour le statut pour les notifications
            $concours->status = 'annulé';

            // Envoyer les notifications d'annulation AVANT de supprimer
            app(\App\Services\ConcoursNotificationService::class)->sendCancellationNotifications($concours);

            // Supprimer le concours de la base de données
            $concours->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Concours annulé et supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de l\'annulation du concours',
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
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des convocations', [
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
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des notifications de surveillance', [
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
     * Retrieve all passed concours (date_concours < today).
     */
    public function passed()
    {
        $today = \Carbon\Carbon::today();
        $concours = \App\Models\Concours::where('date_concours', '<', $today)
            ->with(['candidats', 'superviseurs', 'professeurs'])
            ->orderBy('date_concours', 'desc')
            ->get();
        return response()->json($concours);
    }

    /**
     * Retrieve all upcoming concours (date_concours >= today).
     */
    public function upcoming()
    {
        // Get all upcoming concours
        $concours = Concours::with(['candidats', 'superviseurs', 'professeurs'])
            ->where('date_concours', '>=', now()->toDateString())
            ->orderBy('date_concours', 'asc')
            ->get();

        return response()->json($concours);
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
            \Illuminate\Support\Facades\Log::error('Error generating concours report PDF: ' . $e->getMessage(), [
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