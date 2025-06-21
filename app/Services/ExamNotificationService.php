<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Exam;
use App\Mail\ExamConvocation;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExamNotificationService
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generateAndSendNotifications(Exam $exam)
    {
        // Load all necessary relationships
        $exam->load(['classrooms', 'module', 'students', 'superviseurs']);

        // Get module name safely
        $moduleName = $exam->module ? $exam->module->module_intitule : 'Module inconnu';

        // Process supervisors if they are passed as a string
        if (property_exists($exam, 'superviseurs') && is_string($exam->getAttribute('superviseurs'))) {
            $supervisorName = trim($exam->getAttribute('superviseurs'));
            $nameParts = explode(' ', $supervisorName);
            $lastName = end($nameParts);
            $firstName = implode(' ', array_slice($nameParts, 0, -1));

            // Try to find or create the supervisor
            $supervisor = \App\Models\Superviseur::firstOrCreate(
                [
                    'nom' => $lastName,
                    'prenom' => $firstName
                ],
                [
                    'departement' => $exam->filiere,
                    'type' => 'normal'
                ]
            );

            // Detach all existing supervisors and attach the new one
            $exam->superviseurs()->detach();
            $exam->superviseurs()->attach($supervisor->id);

            // Force reload the superviseurs relationship
            $exam->load('superviseurs');

            Log::info('Supervisor attached to exam', [
                'supervisor_id' => $supervisor->id,
                'name' => $supervisor->prenom . ' ' . $supervisor->nom,
                'exam_id' => $exam->id
            ]);
        }

        // Ensure $exam->superviseurs is a collection before sending notifications
        if (!($exam->superviseurs instanceof \Illuminate\Support\Collection)) {
            $exam->load('superviseurs');
        }

        Log::info('Début de l\'envoi des notifications', [
            'exam_id' => $exam->id,
            'students_count' => $exam->students->count(),
            'supervisors_count' => $exam->superviseurs instanceof \Illuminate\Database\Eloquent\Collection ? $exam->superviseurs->count() : 0,
            'module' => $moduleName,
            'classrooms' => $exam->classrooms->pluck('nom_du_local')
        ]);

        // Send notifications to supervisors first
        if ($exam->superviseurs instanceof \Illuminate\Support\Collection && $exam->superviseurs->count() > 0) {
            foreach ($exam->superviseurs as $supervisor) {
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

                    Mail::to($supervisor->email)->send(new \App\Mail\ExamSurveillanceNotification(
                        $exam,
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
        } else {
            Log::error('No valid supervisors found for exam', ['exam_id' => $exam->id, 'superviseurs' => $exam->superviseurs]);
        }

        // Then process student notifications
        foreach ($exam->students as $student) {
            try {
                Log::info('Traitement de l\'étudiant', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'cne' => $student->cne,
                    'qr_code' => $student->qr_code
                ]);

                // Vérifier et générer le QR code si nécessaire
                if (empty($student->qr_code) || !Storage::exists('public/' . $student->qr_code)) {
                    $qrData = [
                        'nom' => $student->prenom ?? '',
                        'prenom' => $student->nom ?? '',
                        'codeApogee' => $student->numero_etudiant ?? '',
                        'cne' => $student->cne ?? 'AA06516'
                    ];

                    // Générer le QR code
                    $qrCodePath = $this->qrCodeService->generateQRCode($qrData);

                    // Mettre à jour l'étudiant avec le nouveau chemin du QR code
                    $student->qr_code = $qrCodePath;
                    $student->save();

                    Log::info('QR code généré et sauvegardé', [
                        'student_id' => $student->id,
                        'qr_code_path' => $qrCodePath,
                        'cne' => $qrData['cne']
                    ]);
                }

                // Vérification que le QR Code existe dans le stockage
                if (!Storage::exists('public/' . $student->qr_code)) {
                    Log::error('QR code non trouvé dans le stockage', [
                        'student_id' => $student->id,
                        'qr_code_path' => $student->qr_code
                    ]);
                    throw new \Exception("QR Code manquant pour l'étudiant {$student->id}");
                }

                // Générer le PDF de la convocation
                $pdf = $this->generateConvocationPDF($student, $exam);
                Log::info('PDF généré avec succès', ['student_id' => $student->id]);

                // Préparer les données pour l'email
                $emailData = [
                    'pdf_data' => $pdf,
                    'exam' => [
                        'name' => $moduleName,
                        'date' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                        'heure_debut' => $exam->heure_debut ? $exam->heure_debut->format('H:i') : 'Heure non spécifiée',
                        'heure_fin' => $exam->heure_fin ? $exam->heure_fin->format('H:i') : 'Heure non spécifiée',
                        'salle' => $exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée'
                    ]
                ];

                Log::info('Envoi de l\'email en cours', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'exam_data' => $emailData['exam']
                ]);

                // Vérifier que les données sont valides avant l'envoi
                if (!is_array($emailData) || !isset($emailData['exam']) || !isset($emailData['pdf_data'])) {
                    Log::error('Données invalides pour l\'email', [
                        'student_id' => $student->id,
                        'email_data' => $emailData
                    ]);
                    throw new \Exception("Les données pour l'email sont invalides");
                }

                // Envoyer l'email avec la pièce jointe
                Mail::to($student->email)->send(new ExamConvocation($student, $emailData));

                Log::info("Email envoyé avec succès à l'étudiant", [
                    'student_id' => $student->id,
                    'email' => $student->email
                ]);
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de la convocation à l'étudiant : {$student->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    // Méthode pour générer le PDF
    protected function generateConvocationPDF(Student $student, Exam $exam)
    {
        // Vérification des données
        if (!is_object($student) || empty($student->nom) || empty($student->prenom) || (empty($student->cne) && empty($student->numero_etudiant))) {
            Log::error('Données étudiant invalides', [
                'student_id' => $student->id,
                'nom' => $student->nom,
                'prenom' => $student->prenom,
                'cne' => $student->cne,
                'numero_etudiant' => $student->numero_etudiant
            ]);
            throw new \Exception("Les données de l'étudiant sont invalides.");
        }

        // Calcul de la durée de l'examen
        $duree = '2h'; // Valeur par défaut
        if ($exam->heure_debut && $exam->heure_fin) {
            try {
                $debut = \Carbon\Carbon::parse($exam->heure_debut);
                $fin = \Carbon\Carbon::parse($exam->heure_fin);
                $diff = $debut->diff($fin);
                $duree = $diff->h . 'h' . ($diff->i > 0 ? $diff->i . 'min' : '');
            } catch (\Exception $e) {
                Log::error('Erreur lors du calcul de la durée', [
                    'error' => $e->getMessage(),
                    'heure_debut' => $exam->heure_debut,
                    'heure_fin' => $exam->heure_fin
                ]);
            }
        }

        $data = [
            'student' => $student,
            'exam' => [
                'name' => $exam->module ? $exam->module->module_intitule : 'Module non spécifié',
                'date' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                'heure_debut' => $exam->heure_debut ? $exam->heure_debut : 'Heure non spécifiée',
                'heure_fin' => $exam->heure_fin ? $exam->heure_fin : 'Heure non spécifiée',
                'salle' => $exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée',
                'session' => 'Printemps', // Session par défaut
                'year' => $exam->date_examen ? $exam->date_examen->format('Y') : date('Y'),
                'month' => $exam->date_examen ? $exam->date_examen->format('F') : date('F'),
                'semestre' => $exam->semestre ?? 'Non spécifié'
            ],
            'examSchedule' => [
                [
                    'jour' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                    'module' => $exam->module ? $exam->module->module_intitule : 'Module non spécifié',
                    'duree' => $duree,
                    'horaire' => $exam->heure_debut ? $exam->heure_debut->format('H:i') . ' - ' . $exam->heure_fin->format('H:i') : 'Horaire non spécifié',
                    'color' => 'green'
                ]
            ],
            'qrCodePath' => asset('storage/' . $student->qr_code)
        ];

        Log::info('Données pour le PDF', [
            'student' => [
                'id' => $student->id,
                'nom' => $student->nom,
                'prenom' => $student->prenom,
                'cne' => $student->cne,
                'numero_etudiant' => $student->numero_etudiant,
                'qr_code' => $student->qr_code
            ],
            'exam' => $data['exam']
        ]);

        // Générer le PDF
        $pdf = PDF::loadView('pdfs.convocation', $data);
        return $pdf->output();
    }

    /**
     * Send notifications only to supervisors for a given exam.
     *
     * @param Exam $exam
     * @return void
     */
    public function sendSupervisorNotifications(Exam $exam)
    {
        // Ensure relationships are loaded
        $exam->load(['superviseurs', 'module', 'classrooms']);

        // Get module name safely
        $moduleName = $exam->module ? $exam->module->module_intitule : 'Module inconnu';

        // Log supervisor notification start
        Log::info('Début de l\'envoi des notifications aux superviseurs', [
            'exam_id' => $exam->id,
            'supervisors_count' => $exam->superviseurs instanceof \Illuminate\Database\Eloquent\Collection ? $exam->superviseurs->count() : 0,
            'module' => $moduleName,
            'classrooms' => $exam->classrooms->pluck('nom_du_local')
        ]);

        // Send notifications to supervisors
        if ($exam->superviseurs instanceof \Illuminate\Support\Collection && $exam->superviseurs->count() > 0) {
            foreach ($exam->superviseurs as $supervisor) {
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

                    Mail::to($supervisor->email)->send(new \App\Mail\ExamSurveillanceNotification(
                        $exam,
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
        } else {
            Log::error('No valid supervisors found for exam', ['exam_id' => $exam->id, 'superviseurs' => $exam->superviseurs]);
        }
    }

    /**
     * Send cancellation notifications to all concerned parties (students, supervisors, professors)
     *
     * @param Exam $exam
     * @return void
     */
    public function sendCancellationNotifications(Exam $exam)
    {
        // Ensure relationships are loaded
        $exam->load(['students', 'module', 'classrooms']);

        // Get module name safely
        $moduleName = $exam->module ? $exam->module->module_intitule : 'Module inconnu';

        Log::info('Début de l\'envoi des notifications d\'annulation', [
            'exam_id' => $exam->id,
            'students_count' => $exam->students->count(),
            'superviseurs_raw' => $exam->superviseurs,
            'professeurs_raw' => $exam->professeurs,
            'module' => $moduleName
        ]);

        // Send cancellation notifications to students
        foreach ($exam->students as $student) {
            try {
                if (empty($student->email)) {
                    Log::error('Student has no email address', [
                        'student_id' => $student->id,
                        'name' => $student->prenom . ' ' . $student->nom
                    ]);
                    continue;
                }

                Mail::to($student->email)->send(new \App\Mail\ExamCancellationNotification(
                    $exam,
                    $student->prenom . ' ' . $student->nom
                ));

                Log::info('Cancellation notification sent successfully to student', [
                    'student_id' => $student->id,
                    'email' => $student->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation notification to student', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send cancellation notifications to supervisors (stored as string)
        if (!empty($exam->superviseurs)) {
            Log::info('Processing supervisors for cancellation', [
                'superviseurs_raw' => $exam->superviseurs
            ]);

            $supervisorNames = array_map('trim', explode(',', $exam->superviseurs));

            Log::info('Supervisor names extracted', [
                'supervisor_names' => $supervisorNames
            ]);

            foreach ($supervisorNames as $supervisorName) {
                if (empty($supervisorName)) continue;

                Log::info('Processing supervisor name', [
                    'supervisor_name' => $supervisorName
                ]);

                // Find supervisor by name
                $nameParts = explode(' ', trim($supervisorName));
                $lastName = end($nameParts);
                $firstName = implode(' ', array_slice($nameParts, 0, -1));

                Log::info('Name parts extracted', [
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);

                $supervisor = \App\Models\Superviseur::where('nom', $lastName)
                    ->where('prenom', $firstName)
                    ->first();

                Log::info('Supervisor lookup result', [
                    'supervisor_found' => $supervisor ? 'yes' : 'no',
                    'supervisor_id' => $supervisor ? $supervisor->id : null,
                    'supervisor_email' => $supervisor ? $supervisor->email : null
                ]);

                if ($supervisor && !empty($supervisor->email)) {
                    try {
                        Mail::to($supervisor->email)->send(new \App\Mail\ExamCancellationNotification(
                            $exam,
                            $supervisor->prenom . ' ' . $supervisor->nom,
                            'supervisor'
                        ));

                        Log::info('Cancellation notification sent successfully to supervisor', [
                            'supervisor_id' => $supervisor->id,
                            'email' => $supervisor->email,
                            'name' => $supervisor->prenom . ' ' . $supervisor->nom
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send cancellation notification to supervisor', [
                            'supervisor_id' => $supervisor->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::error('Supervisor not found or has no email', [
                        'supervisor_name' => $supervisorName,
                        'found_supervisor' => $supervisor ? $supervisor->id : 'not found',
                        'has_email' => $supervisor ? !empty($supervisor->email) : false
                    ]);
                }
            }
        } else {
            Log::info('No supervisors found for exam', [
                'exam_id' => $exam->id,
                'superviseurs_field' => $exam->superviseurs
            ]);
        }

        // Send cancellation notifications to professors (stored as string)
        if (!empty($exam->professeurs)) {
            $professeurNames = array_map('trim', explode(',', $exam->professeurs));

            foreach ($professeurNames as $professeurName) {
                if (empty($professeurName)) continue;

                // Find professeur by name
                $nameParts = explode(' ', trim($professeurName));
                $lastName = end($nameParts);
                $firstName = implode(' ', array_slice($nameParts, 0, -1));

                $professeur = \App\Models\Professeur::where('nom', $lastName)
                    ->where('prenom', $firstName)
                    ->first();

                if ($professeur && !empty($professeur->email)) {
                    try {
                        Mail::to($professeur->email)->send(new \App\Mail\ExamCancellationNotification(
                            $exam,
                            $professeur->prenom . ' ' . $professeur->nom,
                            'professeur'
                        ));

                        Log::info('Cancellation notification sent successfully to professeur', [
                            'professeur_id' => $professeur->id,
                            'email' => $professeur->email,
                            'name' => $professeur->prenom . ' ' . $professeur->nom
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send cancellation notification to professeur', [
                            'professeur_id' => $professeur->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::error('Professeur not found or has no email', [
                        'professeur_name' => $professeurName,
                        'found_professeur' => $professeur ? $professeur->id : 'not found',
                        'has_email' => $professeur ? !empty($professeur->email) : false
                    ]);
                }
            }
        }
    }

    /**
     * Send update notifications to professors and supervisors
     *
     * @param Exam $exam
     * @return void
     */
    public function sendUpdateNotifications(Exam $exam)
    {
        // Ensure relationships are loaded
        $exam->load(['module', 'classrooms']);

        // Get module name safely
        $moduleName = $exam->module ? $exam->module->module_intitule : 'Module inconnu';

        Log::info('Début de l\'envoi des notifications de mise à jour', [
            'exam_id' => $exam->id,
            'superviseurs_raw' => $exam->superviseurs,
            'professeurs_raw' => $exam->professeurs,
            'module' => $moduleName
        ]);

        // Send update notifications to supervisors (stored as string)
        if (!empty($exam->superviseurs)) {
            $supervisorNames = array_map('trim', explode(',', $exam->superviseurs));

            foreach ($supervisorNames as $supervisorName) {
                if (empty($supervisorName)) continue;

                // Find supervisor by name
                $nameParts = explode(' ', trim($supervisorName));
                $lastName = end($nameParts);
                $firstName = implode(' ', array_slice($nameParts, 0, -1));

                $supervisor = \App\Models\Superviseur::where('nom', $lastName)
                    ->where('prenom', $firstName)
                    ->first();

                if ($supervisor && !empty($supervisor->email)) {
                    try {
                        Mail::to($supervisor->email)->send(new \App\Mail\ExamUpdateNotification(
                            $exam,
                            $supervisor->prenom . ' ' . $supervisor->nom,
                            'supervisor'
                        ));

                        Log::info('Update notification sent successfully to supervisor', [
                            'supervisor_id' => $supervisor->id,
                            'email' => $supervisor->email,
                            'name' => $supervisor->prenom . ' ' . $supervisor->nom
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send update notification to supervisor', [
                            'supervisor_id' => $supervisor->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::error('Supervisor not found or has no email', [
                        'supervisor_name' => $supervisorName,
                        'found_supervisor' => $supervisor ? $supervisor->id : 'not found',
                        'has_email' => $supervisor ? !empty($supervisor->email) : false
                    ]);
                }
            }
        }

        // Send update notifications to professors (stored as string)
        if (!empty($exam->professeurs)) {
            $professeurNames = array_map('trim', explode(',', $exam->professeurs));

            foreach ($professeurNames as $professeurName) {
                if (empty($professeurName)) continue;

                // Find professeur by name
                $nameParts = explode(' ', trim($professeurName));
                $lastName = end($nameParts);
                $firstName = implode(' ', array_slice($nameParts, 0, -1));

                $professeur = \App\Models\Professeur::where('nom', $lastName)
                    ->where('prenom', $firstName)
                    ->first();

                if ($professeur && !empty($professeur->email)) {
                    try {
                        Mail::to($professeur->email)->send(new \App\Mail\ExamUpdateNotification(
                            $exam,
                            $professeur->prenom . ' ' . $professeur->nom,
                            'professeur'
                        ));

                        Log::info('Update notification sent successfully to professeur', [
                            'professeur_id' => $professeur->id,
                            'email' => $professeur->email,
                            'name' => $professeur->prenom . ' ' . $professeur->nom
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send update notification to professeur', [
                            'professeur_id' => $professeur->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::error('Professeur not found or has no email', [
                        'professeur_name' => $professeurName,
                        'found_professeur' => $professeur ? $professeur->id : 'not found',
                        'has_email' => $professeur ? !empty($professeur->email) : false
                    ]);
                }
            }
        }
    }

    /**
     * Send updated convocations to students after exam update
     *
     * @param Exam $exam
     * @return void
     */
    public function sendUpdatedConvocations(Exam $exam)
    {
        // Ensure relationships are loaded
        $exam->load(['students', 'module', 'classrooms']);

        // Get module name safely
        $moduleName = $exam->module ? $exam->module->module_intitule : 'Module inconnu';

        Log::info('Début de l\'envoi des convocations mises à jour', [
            'exam_id' => $exam->id,
            'students_count' => $exam->students->count(),
            'module' => $moduleName
        ]);

        // Send updated convocations to students
        foreach ($exam->students as $student) {
            try {
                if (empty($student->email)) {
                    Log::error('Student has no email address', [
                        'student_id' => $student->id,
                        'name' => $student->prenom . ' ' . $student->nom
                    ]);
                    continue;
                }

                // Generate updated PDF convocation
                $pdf = $this->generateConvocationPDF($student, $exam);

                // Prepare email data
                $emailData = [
                    'pdf_data' => $pdf,
                    'exam' => [
                        'name' => $moduleName,
                        'date' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                        'heure_debut' => $exam->heure_debut ? $exam->heure_debut->format('H:i') : 'Heure non spécifiée',
                        'heure_fin' => $exam->heure_fin ? $exam->heure_fin->format('H:i') : 'Heure non spécifiée',
                        'salle' => $exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée'
                    ]
                ];

                // Send updated convocation
                Mail::to($student->email)->send(new \App\Mail\ExamConvocation($student, $emailData));

                Log::info('Updated convocation sent successfully to student', [
                    'student_id' => $student->id,
                    'email' => $student->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send updated convocation to student', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}