<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation pour la Surveillance du Concours</title>
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
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
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
        <h1>Convocation pour la Surveillance du Concours</h1>
        <p>Faculté des Sciences et Techniques</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $personName }}</strong>,</p>

        <p>Nous vous informons que vous avez été désigné(e) comme <strong>{{ $role }}</strong> pour le concours
            suivant :</p>

        <div class="info-box">
            <h3>{{ $concours->titre }}</h3>
            <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($concours->date_concours)->format('d/m/Y') }}</p>
            <p><strong>Heure :</strong> {{ \Carbon\Carbon::parse($concours->heure_debut)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($concours->heure_fin)->format('H:i') }}</p>
            <p><strong>Local :</strong> {{ $concours->locaux ?: 'Non spécifié' }}</p>
            <p><strong>Type d'épreuve :</strong> {{ $concours->type_epreuve }}</p>
            @if ($concours->description)
                <p><strong>Description :</strong> {{ $concours->description }}</p>
            @endif
        </div>

        <p class="important">Veuillez vous présenter 30 minutes avant l'heure de début pour les préparatifs.</p>

        <p><strong>Consignes importantes :</strong></p>
        <ul>
            <li>Vérifiez l'identité des candidats</li>
            <li>Assurez-vous du bon déroulement de l'épreuve</li>
            <li>Respectez les consignes de sécurité</li>
            <li>Signalez tout incident au responsable</li>
        </ul>

        <p>Merci pour votre collaboration.</p>

        <p>Cordialement,<br>
            <strong>L'équipe pédagogique</strong><br>
            Faculté des Sciences et Techniques
        </p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>Pour toute question, veuillez contacter le secrétariat.</p>
    </div>
</body>

</html>
