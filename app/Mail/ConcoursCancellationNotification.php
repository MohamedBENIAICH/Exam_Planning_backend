<?php

namespace App\Mail;

use App\Models\Concours;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConcoursCancellationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $concours;
    public $recipientName;
    public $recipientType;

    /**
     * Create a new message instance.
     */
    public function __construct(Concours $concours, string $recipientName, string $recipientType = 'candidat')
    {
        $this->concours = $concours;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Annulation du concours - {$this->concours->titre}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match ($this->recipientType) {
            'candidat' => 'emails.concours.cancellation-candidat',
            'supervisor' => 'emails.concours.cancellation-supervisor',
            'professeur' => 'emails.concours.cancellation-professeur',
            default => 'emails.concours.cancellation-candidat'
        };

        // Format locaux for display
        $locauxDisplay = 'Local non spécifié';
        if ($this->concours->locaux) {
            if (is_array($this->concours->locaux)) {
                // If it's an array of objects with nom_local
                $locauxDisplay = collect($this->concours->locaux)
                    ->map(function ($local) {
                        if (is_array($local) && isset($local['nom_local'])) {
                            return $local['nom_local'];
                        } elseif (is_object($local) && isset($local->nom_local)) {
                            return $local->nom_local;
                        }
                        return null;
                    })
                    ->filter()
                    ->join(', ');
            } elseif (is_string($this->concours->locaux)) {
                $locauxDisplay = $this->concours->locaux;
            }
        }

        return new Content(
            view: $view,
            with: [
                'concours' => $this->concours,
                'recipientName' => $this->recipientName,
                'recipientType' => $this->recipientType,
                'date' => $this->concours->date_concours ? \Carbon\Carbon::parse($this->concours->date_concours)->format('d/m/Y') : 'Date non spécifiée',
                'heure_debut' => $this->concours->heure_debut ? \Carbon\Carbon::parse($this->concours->heure_debut)->format('H:i') : 'Heure non spécifiée',
                'heure_fin' => $this->concours->heure_fin ? \Carbon\Carbon::parse($this->concours->heure_fin)->format('H:i') : 'Heure non spécifiée',
                'locaux' => $locauxDisplay ?: 'Local non spécifié',
                'type_epreuve' => $this->concours->type_epreuve
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
