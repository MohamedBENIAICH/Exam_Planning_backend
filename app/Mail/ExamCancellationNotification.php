<?php

namespace App\Mail;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamCancellationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $exam;
    public $recipientName;
    public $recipientType;

    /**
     * Create a new message instance.
     */
    public function __construct(Exam $exam, string $recipientName, string $recipientType = 'student')
    {
        $this->exam = $exam;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $moduleName = $this->exam->module ? $this->exam->module->module_intitule : 'Module inconnu';

        return new Envelope(
            subject: "Annulation de l'examen - {$moduleName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match ($this->recipientType) {
            'student' => 'emails.exam.cancellation-student',
            'supervisor' => 'emails.exam.cancellation-supervisor',
            'professeur' => 'emails.exam.cancellation-professeur',
            default => 'emails.exam.cancellation-student'
        };

        return new Content(
            view: $view,
            with: [
                'exam' => $this->exam,
                'recipientName' => $this->recipientName,
                'recipientType' => $this->recipientType,
                'moduleName' => $this->exam->module ? $this->exam->module->module_intitule : 'Module inconnu',
                'date' => $this->exam->date_examen ? $this->exam->date_examen->format('d/m/Y') : 'Date non spécifiée',
                'heure_debut' => $this->exam->heure_debut ? $this->exam->heure_debut->format('H:i') : 'Heure non spécifiée',
                'heure_fin' => $this->exam->heure_fin ? $this->exam->heure_fin->format('H:i') : 'Heure non spécifiée',
                'salle' => $this->exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée'
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
