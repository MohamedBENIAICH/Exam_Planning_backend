<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Exam;
use App\Mail\ExamConvocation;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;  // Ajoute cette ligne

class ExamNotificationService
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generateAndSendNotifications(Exam $exam)
    {
        $students = $exam->students;
        Log::info('Début de l\'envoi des notifications', ['exam_id' => $exam->id, 'students_count' => count($students)]);

        foreach ($students as $student) {
            try {
                Log::info('Traitement de l\'étudiant', [
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'qr_code' => $student->qr_code
                ]);

                // Vérifier et générer le QR code si nécessaire
                if (empty($student->qr_code) || !Storage::exists('public/' . $student->qr_code)) {
                    $qrData = [
                        'cne' => $student->cne ?? $student->numero_etudiant ?? '',
                        'nom' => $student->nom ?? '',
                        'prenom' => $student->prenom ?? ''
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
                        'name' => $exam->module ?? 'Module non spécifié',
                        'date' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                        'heure_debut' => $exam->heure_debut ? $exam->heure_debut : 'Heure non spécifiée',
                        'heure_fin' => $exam->heure_fin ? $exam->heure_fin : 'Heure non spécifiée',
                        'salle' => $exam->classrooms->first() ? $exam->classrooms->first()->nom : 'Salle non spécifiée'
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

        $data = [
            'student' => $student,
            'exam' => [
                'name' => $exam->module ?? 'Module non spécifié',
                'date' => $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                'heure_debut' => $exam->heure_debut ? $exam->heure_debut : 'Heure non spécifiée',
                'heure_fin' => $exam->heure_fin ? $exam->heure_fin : 'Heure non spécifiée',
                'salle' => $exam->classrooms->first() ? $exam->classrooms->first()->nom : 'Salle non spécifiée'
            ],
            'qrCodePath' => Storage::url('public/' . $student->qr_code)
        ];

        // Générer le PDF
        $pdf = PDF::loadView('pdfs.convocation', $data);
        return $pdf->output();
    }
}
