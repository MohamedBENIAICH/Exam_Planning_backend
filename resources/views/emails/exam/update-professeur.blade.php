<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour d'examen - Professeur</title>
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
        <h1>📝 Mise à jour d'examen - Professeur</h1>
    </div>

    <div class="content">
        <p>Bonjour monsieur {{ $recipientName }},</p>

        <p>Nous vous informons que des modifications ont été apportées à l'examen suivant :</p>

        <div class="important">
            <h3>Détails mis à jour de l'examen :</h3>
            <ul>
                <li><strong>Module :</strong> {{ $moduleName }}</li>
                <li><strong>Date :</strong> {{ $date }}</li>
                <li><strong>Heure de début :</strong> {{ $heure_debut }}</li>
                <li><strong>Heure de fin :</strong> {{ $heure_fin }}</li>
                <li><strong>Salle :</strong> {{ $salle }}</li>
            </ul>
        </div>

        <p><strong>Important :</strong> Cet examen ne se déroulera pas à la date prévue. Une nouvelle date sera
            communiquée ultérieurement.</p>

        <p>Les étudiants ont été informés de ces changements.</p>

        <p>Cordialement,<br>
            L'équipe de gestion des examens</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Faculté des Sciences et Techniques de Marrakech - Gestion des Examens</p>
    </div>
</body>

</html>
