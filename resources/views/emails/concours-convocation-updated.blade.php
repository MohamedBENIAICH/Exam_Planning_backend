<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation mise à jour - Concours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #ff6b35;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #ff6b35;
            margin-bottom: 10px;
        }

        .update-banner {
            background-color: #fff3cd;
            border: 2px solid #ff6b35;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }

        .update-banner h2 {
            color: #ff6b35;
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .content {
            margin-bottom: 30px;
        }

        .info-box {
            background-color: #e8f4fd;
            border: 1px solid #ff6b35;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .important {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .qr-notice {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
        }

        .qr-notice h4 {
            color: #ff6b35;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="title">📝 Convocation mise à jour - Concours</h1>
            <p>Faculté des Sciences et Techniques de Marrakech</p>
        </div>

        <div class="update-banner">
            <h2>CONVOCATION MISE À JOUR</h2>
            <p><strong>Cette convocation remplace la précédente.</strong><br>
                Veuillez prendre note des modifications apportées au concours.</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $candidat->prenom }} {{ $candidat->nom }}</strong>,</p>

            <p>Nous vous informons que des modifications ont été apportées au concours suivant et qu'une nouvelle
                convocation vous est envoyée :</p>

            <div class="info-box">
                <h3>{{ $concours['titre'] }}</h3>
                <p><strong>Date :</strong> {{ $concours['date'] }}</p>
                <p><strong>Heure :</strong> {{ $concours['heure_debut'] }} - {{ $concours['heure_fin'] }}</p>
                <p><strong>Locaux :</strong>
                    @php
                        $locaux = json_decode($concours['locaux'], true);
                        if (is_array($locaux)) {
                            // Chercher 'nom_local' ou 'nom_du_local' pour plus de flexibilité
                            $localKeys = array_column($locaux, 'nom_du_local');
                            if (empty(array_filter($localKeys))) {
                                $localKeys = array_column($locaux, 'nom_local');
                            }
                            echo implode(', ', $localKeys);
                        } else {
                            echo $concours['locaux'];
                        }
                    @endphp
                </p>
                <p><strong>Type d'épreuve :</strong> {{ $concours['type_epreuve'] }}</p>
            </div>

            <div class="qr-notice">
                <h4>QR Code mis à jour</h4>
                <p>Le QR code sur la nouvelle convocation a été mis à jour avec vos informations d'identification
                    actuelles.</p>
            </div>

            <p class="important"><strong>Important :</strong></p>
            <ul>
                <li>Cette convocation <strong>remplace complètement</strong> la précédente</li>
                <li>Présentez-vous 30 minutes avant l'heure de début</li>
                <li>Apportez <strong>cette nouvelle convocation</strong> et une pièce d'identité</li>
                <li>Le QR code a été mis à jour avec vos informations actuelles</li>
                <li>Respectez les consignes de sécurité</li>
            </ul>

            <p>Vous trouverez ci-joint le PDF contenant votre convocation mise à jour. Veuillez présenter cette nouvelle
                convocation et votre pièce d'identité le jour du concours.</p>

            <p>Nous vous souhaitons bonne chance pour ce concours.</p>

            <p>Cordialement,<br>
                <strong>L'équipe pédagogique</strong><br>
                Faculté des Sciences et Techniques de Marrakech
            </p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement suite à une modification du concours. Merci de ne pas y répondre.
            </p>
            <p>Pour toute question, veuillez contacter le secrétariat de votre formation.</p>
        </div>
    </div>
</body>

</html>
