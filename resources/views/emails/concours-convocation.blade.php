<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation au concours</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .footer {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #6c757d;
        }

        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 15px 0;
        }

        .important {
            font-weight: bold;
            color: #d32f2f;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Convocation au concours</h1>
        <p>Faculté des Sciences et Techniques</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $candidat->prenom }} {{ $candidat->nom }}</strong>,</p>

        <p>Nous vous informons que vous êtes convoqué(e) au concours suivant :</p>

        <div class="info-box">
            <h3>{{ $concours['titre'] }}</h3>
            <p><strong>Date :</strong> {{ $concours['date'] }}</p>
            <p><strong>Heure :</strong> {{ $concours['heure_debut'] }} - {{ $concours['heure_fin'] }}</p>
            <p><strong>Local :</strong> {{ $concours['locaux'] }}</p>
            <p><strong>Type d'épreuve :</strong> {{ $concours['type_epreuve'] }}</p>
        </div>

        <p class="important">Vous trouverez ci-joint le PDF contenant votre convocation. Veuillez présenter cette
            convocation et votre pièce d'identité le jour du concours.</p>

        <p><strong>Important :</strong></p>
        <ul>
            <li>Présentez-vous 30 minutes avant l'heure de début</li>
            <li>Apportez votre convocation et une pièce d'identité</li>
            <li>Respectez les consignes de sécurité</li>
        </ul>

        <p>Nous vous souhaitons bonne chance pour ce concours.</p>

        <p>Cordialement,<br>
            <strong>L'équipe pédagogique</strong><br>
            Faculté des Sciences et Techniques
        </p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Pour toute question, veuillez contacter le secrétariat de votre formation.</p>
    </div>
</body>

</html>
