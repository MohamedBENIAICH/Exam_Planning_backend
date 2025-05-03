<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Convocation à l'examen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .content {
            margin-bottom: 30px;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Convocation à l'examen</h1>
    </div>

    <div class="content">
        <p>Cher(e) étudiant(e),</p>

        <p>Vous êtes convoqué(e) à l'examen suivant :</p>

        <ul>
            <li><strong>Module :</strong> {{ $exam['name'] }}</li>
            <li><strong>Date :</strong> {{ $exam['date'] }}</li>
            <li><strong>Heure de début :</strong> {{ $exam['heure_debut'] }}</li>
            <li><strong>Heure de fin :</strong> {{ $exam['heure_fin'] }}</li>
            <li><strong>Salle :</strong> {{ $exam['salle'] }}</li>
        </ul>

        <p>Vos informations :</p>
        <ul>
            <li><strong>CNE :</strong> {{ $student->cne }}</li>
            <li><strong>Nom :</strong> {{ $student->nom }}</li>
            <li><strong>Prénom :</strong> {{ $student->prenom }}</li>
        </ul>
    </div>

    <div class="qr-code">
        <img src="{{ $qrCodePath }}" alt="QR Code" style="width: 200px;">
    </div>

    <div class="footer">
        <p>Veuillez présenter cette convocation et votre carte d'étudiant le jour de l'examen.</p>
        <p>Merci de votre compréhension.</p>
    </div>
</body>

</html>
