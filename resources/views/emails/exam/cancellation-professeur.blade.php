<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation d'examen - Professeur</title>
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
        <h1>🚫 Annulation d'examen - Professeur</h1>
    </div>

    <div class="content">
        <p>Bonjour monsieur {{ $recipientName }},</p>

        <p>Nous vous informons que l'examen suivant a été <strong>annulé</strong> :</p>

        <div class="important">
            <h3>Détails de l'examen annulé :</h3>
            <ul>
                <li><strong>Module :</strong> {{ $moduleName }}</li>
                <li><strong>Date prévue :</strong> {{ $date }}</li>
                <li><strong>Heure de début :</strong> {{ $heure_debut }}</li>
                <li><strong>Heure de fin :</strong> {{ $heure_fin }}</li>
                <li><strong>Salle :</strong> {{ $salle }}</li>
            </ul>
        </div>

        <p><strong>Important :</strong> Cet examen ne se déroulera pas à la date prévue. Une nouvelle date sera
            communiquée ultérieurement.</p>

        <p>Les étudiants concernés ont été informés de cette annulation.</p>

        <p>Cordialement,<br>
            L'équipe de gestion des examens</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Faculté des Sciences et Techniques de Marrakech - Gestion des Examens</p>
    </div>
</body>

</html>
