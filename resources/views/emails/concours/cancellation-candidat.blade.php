<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation de concours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }

        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }

        .important {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🚫 Annulation de concours</h1>
    </div>

    <div class="content">
        <p>Bonjour {{ $recipientName }},</p>

        <p>Nous vous informons que le concours suivant a été <strong>annulé</strong> :</p>

        <div class="important">
            <h3>Détails du concours annulé :</h3>
            <ul>
                <li><strong>Titre :</strong> {{ $concours->titre }}</li>
                <li><strong>Date prévue :</strong> {{ $date }}</li>
                <li><strong>Heure de début :</strong> {{ $heure_debut }}</li>
                <li><strong>Heure de fin :</strong> {{ $heure_fin }}</li>
                <li><strong>Local :</strong> {{ $locaux }}</li>
                <li><strong>Type d'épreuve :</strong> {{ $type_epreuve }}</li>
            </ul>
        </div>

        <p><strong>Important :</strong> Ce concours ne se déroulera pas à la date prévue. Une nouvelle date sera
            communiquée ultérieurement.</p>

        <p>Nous vous prions de nous excuser pour la gêne occasionnée et vous remercions de votre compréhension.</p>

        <p>Cordialement,<br>
            L'équipe de gestion des concours</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Faculté des Sciences et Techniques de Marrakech - Gestion des Concours</p>
    </div>
</body>

</html>
