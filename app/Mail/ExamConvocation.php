<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExamConvocation extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $pdfContent;

    /**
     * Create a new message instance.
     *
     * @param Student $student
     * @param mixed $pdfContent
     */
    public function __construct(Student $student, $pdfContent)
    {
        $this->student = $student;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Convocation à l\'examen',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        // Log pour déboguer
        \Illuminate\Support\Facades\Log::info('ExamConvocation content', [
            'pdfContent' => $this->pdfContent,
            'exam' => isset($this->pdfContent['exam']) ? $this->pdfContent['exam'] : null
        ]);

        // Vérifier si les données sont valides
        if (!is_array($this->pdfContent) || !isset($this->pdfContent['exam'])) {
            \Illuminate\Support\Facades\Log::error('Données invalides dans ExamConvocation', [
                'pdfContent' => $this->pdfContent
            ]);
            throw new \Exception("Les données de l'examen sont invalides");
        }

        // Message dynamique personnalisé
        $message = "Bonjour " . $this->student->prenom . " " . $this->student->nom . ",\n\n" .
            "Nous vous informons que vous êtes convoqué à l'examen : " . $this->pdfContent['exam']['name'] . ".\n\n" .
            "Date : " . $this->pdfContent['exam']['date'] . "\n" .
            "Heure : " . $this->pdfContent['exam']['heure_debut'] . " - " . $this->pdfContent['exam']['heure_fin'] . "\n" .
            "Salle : " . $this->pdfContent['exam']['salle'] . "\n\n" .
            "Veuillez trouver ci-joint votre convocation sous forme de PDF.\n\n" .
            "Nous vous souhaitons bonne chance pour cet examen.\n\n" .
            "Cordialement,\nL'équipe pédagogique";

        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.convocation', // Vue pour l'email
            with: [
                'student' => $this->student,
                'exam' => $this->pdfContent['exam'],
                'qrCodePath' => Storage::url('public/' . $this->student->qr_code)
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments()
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn() => $this->pdfContent['pdf_data'], // Données PDF du fichier
                'convocation.pdf' // Nom du fichier attaché
            )->withMime('application/pdf'),
        ];
    }
}