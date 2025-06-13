<?php

namespace App\Mail;

use App\Models\Concours;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConcoursSurveillanceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $concours;
    public $personName;
    public $role;

    /**
     * Create a new message instance.
     *
     * @param Concours $concours
     * @param string $personName
     * @param string $role
     */
    public function __construct(Concours $concours, string $personName, string $role = 'superviseur')
    {
        $this->concours = $concours;
        $this->personName = $personName;
        $this->role = $role;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $roleText = $this->role === 'professeur' ? 'Professeur' : 'Superviseur';
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: "Convocation pour la Surveillance du Concours - {$roleText}",
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $roleText = $this->role === 'professeur' ? 'Professeur' : 'Superviseur';

        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.concours-surveillance-notification',
            with: [
                'concours' => $this->concours,
                'personName' => $this->personName,
                'role' => $roleText
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
        return [];
    }
}
