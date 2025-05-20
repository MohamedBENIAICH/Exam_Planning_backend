<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Exam;

class ExamSurveillanceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $exam;
    public $surveillantName;

    public function __construct(Exam $exam, string $surveillantName)
    {
        $this->exam = $exam;
        $this->surveillantName = $surveillantName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convocation pour la Surveillance d\'Examen'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exam-surveillance-notification',
        );
    }
}
