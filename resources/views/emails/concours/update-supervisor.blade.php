<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de concours - Surveillance</title>
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
            background-color: #007bff;
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
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📝 Mise à jour de concours - Surveillance</h1>
    </div>

    <div class="content">
        <p>Bonjour {{ $recipientName }},</p>

        <p>Nous vous informons que des modifications ont été apportées au concours suivant pour lequel vous êtes
            assigné(e) à la surveillance :</p>

        <div class="important">
            <h3>Détails mis à jour du concours :</h3>
            <ul>
                <li><strong>Titre :</strong> {{ $concours->titre }}</li>
                <li><strong>Date :</strong> {{ $date }}</li>
                <li><strong>Heure de début :</strong> {{ $heure_debut }}</li>
                <li><strong>Heure de fin :</strong> {{ $heure_fin }}</li>
                <li><strong>Local :</strong> {{ $locaux }}</li>
                <li><strong>Type d'épreuve :</strong> {{ $type_epreuve }}</li>
            </ul>
        </div>

        <p><strong>Important :</strong> Veuillez prendre note de ces modifications et vous assurer d'être présent(e) aux
            nouvelles conditions.</p>

        <p>Une nouvelle convocation sera envoyée aux candidats avec les informations mises à jour.</p>

        <p>Cordialement,<br>
            L'équipe de gestion des concours</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Faculté des Sciences et Techniques de Marrakech - Gestion des Concours</p>
    </div>
</body>

</html>
