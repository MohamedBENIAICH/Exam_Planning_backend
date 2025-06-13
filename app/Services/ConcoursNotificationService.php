<?php

namespace App\Services;

use App\Models\Candidat;
use App\Models\Concours;
use App\Mail\ConcoursConvocation;
use App\Mail\ConcoursSurveillanceNotification;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ConcoursNotificationService
{
    public function generateAndSendNotifications(Concours $concours)
    {
        // Load all necessary relationships
        $concours->load(['candidats', 'superviseurs', 'professeurs']);

        Log::info('Début de l\'envoi des notifications pour le concours', [
            'concours_id' => $concours->id,
            'candidats_count' => $concours->candidats->count(),
            'superviseurs_count' => $concours->superviseurs->count(),
            'professeurs_count' => $concours->professeurs->count(),
            'titre' => $concours->titre,
            'locaux' => $concours->locaux
        ]);

        // Send notifications to supervisors first
        if ($concours->superviseurs->count() > 0) {
            foreach ($concours->superviseurs as $supervisor) {
                try {
                    if (empty($supervisor->email)) {
                        Log::error('Supervisor has no email address', [
                            'supervisor_id' => $supervisor->id,
                            'name' => $supervisor->prenom . ' ' . $supervisor->nom
                        ]);
                        continue;
                    }

                    Log::info('Sending notification to supervisor', [
                        'supervisor_id' => $supervisor->id,
                        'email' => $supervisor->email,
                        'name' => $supervisor->prenom . ' ' . $supervisor->nom
                    ]);

                    Mail::to($supervisor->email)->send(new ConcoursSurveillanceNotification(
                        $concours,
                        $supervisor->prenom . ' ' . $supervisor->nom
                    ));

                    Log::info('Notification sent successfully to supervisor', [
                        'supervisor_id' => $supervisor->id,
                        'email' => $supervisor->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification to supervisor', [
                        'supervisor_id' => $supervisor->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Send notifications to professors
        if ($concours->professeurs->count() > 0) {
            foreach ($concours->professeurs as $professeur) {
                try {
                    if (empty($professeur->email)) {
                        Log::error('Professeur has no email address', [
                            'professeur_id' => $professeur->id,
                            'name' => $professeur->prenom . ' ' . $professeur->nom
                        ]);
                        continue;
                    }

                    Log::info('Sending notification to professeur', [
                        'professeur_id' => $professeur->id,
                        'email' => $professeur->email,
                        'name' => $professeur->prenom . ' ' . $professeur->nom
                    ]);

                    Mail::to($professeur->email)->send(new ConcoursSurveillanceNotification(
                        $concours,
                        $professeur->prenom . ' ' . $professeur->nom,
                        'professeur'
                    ));

                    Log::info('Notification sent successfully to professeur', [
                        'professeur_id' => $professeur->id,
                        'email' => $professeur->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification to professeur', [
                        'professeur_id' => $professeur->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Then process candidate notifications
        foreach ($concours->candidats as $candidat) {
            try {
                Log::info('Traitement du candidat', [
                    'candidat_id' => $candidat->id,
                    'email' => $candidat->email,
                    'cne' => $candidat->CNE,
                ]);

                // Générer le PDF de la convocation
                $pdf = $this->generateConvocationPDF($candidat, $concours);
                Log::info('PDF généré avec succès', ['candidat_id' => $candidat->id]);

                // Préparer les données pour l'email
                $emailData = [
                    'pdf_data' => $pdf,
                    'concours' => [
                        'titre' => $concours->titre,
                        'date' => $concours->date_concours ? \Carbon\Carbon::parse($concours->date_concours)->format('d/m/Y') : 'Date non spécifiée',
                        'heure_debut' => $concours->heure_debut ? \Carbon\Carbon::parse($concours->heure_debut)->format('H:i') : 'Heure non spécifiée',
                        'heure_fin' => $concours->heure_fin ? \Carbon\Carbon::parse($concours->heure_fin)->format('H:i') : 'Heure non spécifiée',
                        'locaux' => $concours->locaux ?: 'Local non spécifié',
                        'type_epreuve' => $concours->type_epreuve
                    ]
                ];

                Log::info('Envoi de l\'email en cours', [
                    'candidat_id' => $candidat->id,
                    'email' => $candidat->email,
                    'concours_data' => $emailData['concours']
                ]);

                // Vérifier que les données sont valides avant l'envoi
                if (!is_array($emailData) || !isset($emailData['concours']) || !isset($emailData['pdf_data'])) {
                    Log::error('Données invalides pour l\'email', [
                        'candidat_id' => $candidat->id,
                        'email_data' => $emailData
                    ]);
                    throw new \Exception("Les données pour l'email sont invalides");
                }

                // Envoyer l'email avec la pièce jointe
                Mail::to($candidat->email)->send(new ConcoursConvocation($candidat, $emailData));

                Log::info("Email envoyé avec succès au candidat", [
                    'candidat_id' => $candidat->id,
                    'email' => $candidat->email
                ]);
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de la convocation au candidat : {$candidat->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    // Méthode pour générer le PDF
    protected function generateConvocationPDF(Candidat $candidat, Concours $concours)
    {
        // Vérification des données
        if (!is_object($candidat) || empty($candidat->nom) || empty($candidat->prenom) || empty($candidat->CNE)) {
            Log::error('Données candidat invalides', [
                'candidat_id' => $candidat->id,
                'nom' => $candidat->nom,
                'prenom' => $candidat->prenom,
                'cne' => $candidat->CNE,
            ]);
            throw new \Exception("Les données du candidat sont invalides.");
        }

        // Calcul de la durée du concours
        $duree = '2h'; // Valeur par défaut
        if ($concours->heure_debut && $concours->heure_fin) {
            try {
                $debut = \Carbon\Carbon::parse($concours->heure_debut);
                $fin = \Carbon\Carbon::parse($concours->heure_fin);
                $diff = $debut->diff($fin);
                $duree = $diff->h . 'h' . ($diff->i > 0 ? $diff->i . 'min' : '');
            } catch (\Exception $e) {
                Log::error('Erreur lors du calcul de la durée', [
                    'error' => $e->getMessage(),
                    'heure_debut' => $concours->heure_debut,
                    'heure_fin' => $concours->heure_fin
                ]);
            }
        }

        $data = [
            'candidat' => $candidat,
            'concours' => [
                'titre' => $concours->titre,
                'date' => $concours->date_concours ? \Carbon\Carbon::parse($concours->date_concours)->format('d/m/Y') : 'Date non spécifiée',
                'heure_debut' => $concours->heure_debut ? \Carbon\Carbon::parse($concours->heure_debut) : 'Heure non spécifiée',
                'heure_fin' => $concours->heure_fin ? \Carbon\Carbon::parse($concours->heure_fin) : 'Heure non spécifiée',
                'locaux' => $concours->locaux ?: 'Local non spécifié',
                'type_epreuve' => $concours->type_epreuve,
                'description' => $concours->description,
                'year' => $concours->date_concours ? \Carbon\Carbon::parse($concours->date_concours)->format('Y') : date('Y'),
                'month' => $concours->date_concours ? \Carbon\Carbon::parse($concours->date_concours)->format('F') : date('F')
            ],
            'concoursSchedule' => [
                [
                    'jour' => $concours->date_concours ? \Carbon\Carbon::parse($concours->date_concours)->format('d/m/Y') : 'Date non spécifiée',
                    'concours' => $concours->titre,
                    'duree' => $duree,
                    'horaire' => $concours->heure_debut ? \Carbon\Carbon::parse($concours->heure_debut)->format('H:i') . ' - ' . \Carbon\Carbon::parse($concours->heure_fin)->format('H:i') : 'Horaire non spécifié',
                    'color' => 'blue'
                ]
            ]
        ];

        Log::info('Données pour le PDF', [
            'candidat' => [
                'id' => $candidat->id,
                'nom' => $candidat->nom,
                'prenom' => $candidat->prenom,
                'cne' => $candidat->CNE
            ],
            'concours' => $data['concours']
        ]);

        // Générer le PDF
        $pdf = PDF::loadView('pdfs.concours-convocation', $data);
        return $pdf->output();
    }

    /**
     * Send notifications only to supervisors and professors for a given concours.
     *
     * @param Concours $concours
     * @return void
     */
    public function sendSurveillanceNotifications(Concours $concours)
    {
        // Ensure relationships are loaded
        $concours->load(['superviseurs', 'professeurs']);

        Log::info('Début de l\'envoi des notifications de surveillance pour le concours', [
            'concours_id' => $concours->id,
            'supervisors_count' => $concours->superviseurs->count(),
            'professeurs_count' => $concours->professeurs->count(),
            'titre' => $concours->titre
        ]);

        // Send notifications to supervisors
        if ($concours->superviseurs->count() > 0) {
            foreach ($concours->superviseurs as $supervisor) {
                try {
                    if (empty($supervisor->email)) {
                        Log::error('Supervisor has no email address', [
                            'supervisor_id' => $supervisor->id,
                            'name' => $supervisor->prenom . ' ' . $supervisor->nom
                        ]);
                        continue;
                    }

                    Mail::to($supervisor->email)->send(new ConcoursSurveillanceNotification(
                        $concours,
                        $supervisor->prenom . ' ' . $supervisor->nom
                    ));

                    Log::info('Notification sent successfully to supervisor', [
                        'supervisor_id' => $supervisor->id,
                        'email' => $supervisor->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification to supervisor', [
                        'supervisor_id' => $supervisor->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Send notifications to professors
        if ($concours->professeurs->count() > 0) {
            foreach ($concours->professeurs as $professeur) {
                try {
                    if (empty($professeur->email)) {
                        Log::error('Professeur has no email address', [
                            'professeur_id' => $professeur->id,
                            'name' => $professeur->prenom . ' ' . $professeur->nom
                        ]);
                        continue;
                    }

                    Mail::to($professeur->email)->send(new ConcoursSurveillanceNotification(
                        $concours,
                        $professeur->prenom . ' ' . $professeur->nom,
                        'professeur'
                    ));

                    Log::info('Notification sent successfully to professeur', [
                        'professeur_id' => $professeur->id,
                        'email' => $professeur->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send notification to professeur', [
                        'professeur_id' => $professeur->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
    }
}
