<?php

namespace App\Mail;

use App\Models\Candidat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConcoursConvocation extends Mailable
{
    use Queueable, SerializesModels;

    public $candidat;
    public $pdfContent;

    /**
     * Create a new message instance.
     *
     * @param Candidat $candidat
     * @param mixed $pdfContent
     */
    public function __construct(Candidat $candidat, $pdfContent)
    {
        $this->candidat = $candidat;
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
            subject: 'Convocation au concours',
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
        \Illuminate\Support\Facades\Log::info('ConcoursConvocation content', [
            'pdfContent' => $this->pdfContent,
            'concours' => isset($this->pdfContent['concours']) ? $this->pdfContent['concours'] : null
        ]);

        // Vérifier si les données sont valides
        if (!is_array($this->pdfContent) || !isset($this->pdfContent['concours'])) {
            \Illuminate\Support\Facades\Log::error('Données invalides dans ConcoursConvocation', [
                'pdfContent' => $this->pdfContent
            ]);
            throw new \Exception("Les données du concours sont invalides");
        }

        // Message dynamique personnalisé
        $message = "Bonjour " . $this->candidat->prenom . " " . $this->candidat->nom . ",\n\n" .
            "Nous vous informons que vous êtes convoqué au concours : " . $this->pdfContent['concours']['titre'] . ".\n\n" .
            "Date : " . $this->pdfContent['concours']['date'] . "\n" .
            "Heure : " . $this->pdfContent['concours']['heure_debut'] . " - " . $this->pdfContent['concours']['heure_fin'] . "\n" .
            "Local : " . $this->pdfContent['concours']['locaux'] . "\n" .
            "Type d'épreuve : " . $this->pdfContent['concours']['type_epreuve'] . "\n\n" .
            "Veuillez trouver ci-joint votre convocation sous forme de PDF.\n\n" .
            "Nous vous souhaitons bonne chance pour ce concours.\n\n" .
            "Cordialement,\nL'équipe pédagogique";

        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.concours-convocation', // Vue pour l'email
            with: [
                'candidat' => $this->candidat,
                'concours' => $this->pdfContent['concours']
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
                'convocation_concours.pdf' // Nom du fichier attaché
            )->withMime('application/pdf'),
        ];
    }
}
