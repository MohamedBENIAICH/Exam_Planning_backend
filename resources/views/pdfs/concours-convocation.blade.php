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
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2196f3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2196f3;
            margin-bottom: 10px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 16px;
            color: #666;
        }

        .content {
            margin-bottom: 30px;
        }

        .candidate-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .concours-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .schedule {
            margin: 30px 0;
        }

        .schedule table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .schedule th,
        .schedule td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .schedule th {
            background-color: #2196f3;
            color: white;
            font-weight: bold;
        }

        .schedule tr:nth-child(even) {
            background-color: #f2f2f2;
        }


        .important {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <<div class="logo">
            @php
                $logoPath = public_path('images/logofst.jpeg');
                $logoBase64 = '';
                if (file_exists($logoPath)) {
                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                }
            @endphp
            @if ($logoBase64)
                <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Logo de la faculté"
                    style="max-width: 200px; height: auto;">
            @else
                <img src="{{ asset('images/logofst.jpeg') }}" alt="Logo de la faculté"
                    style="max-width: 200px; height: auto;">
            @endif
    </div>
    <div class="title">CONVOCATION AU CONCOURS</div>
    <div class="subtitle">Année universitaire {{ $concours['year'] }}</div>
    </div>

    <div class="content">
        <div class="candidate-info">
            <h3>Informations du candidat :</h3>
            <p><strong>Nom :</strong> {{ $candidat->nom }}</p>
            <p><strong>Prénom :</strong> {{ $candidat->prenom }}</p>
            <p><strong>CNE :</strong> {{ $candidat->CNE }}</p>
            @if ($candidat->CIN)
                <p><strong>CIN :</strong> {{ $candidat->CIN }}</p>
            @endif
        </div>

        <div class="concours-info">
            <h3>Détails du concours :</h3>
            <p><strong>Titre :</strong> {{ $concours['titre'] }}</p>
            <p><strong>Date :</strong> {{ $concours['date'] }}</p>
            <p><strong>Heure de début :</strong> {{ $concours['heure_debut'] }}</p>
            <p><strong>Heure de fin :</strong> {{ $concours['heure_fin'] }}</p>
            <p><strong>Local :</strong> {{ $concours['locaux'] }}</p>
            <p><strong>Type d'épreuve :</strong> {{ $concours['type_epreuve'] }}</p>
            @if ($concours['description'])
                <p><strong>Description :</strong> {{ $concours['description'] }}</p>
            @endif
        </div>

        <div class="schedule">
            <h3>Planning du concours :</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Concours</th>
                        <th>Horaire</th>
                        <th>Durée</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concoursSchedule as $schedule)
                        <tr>
                            <td>{{ $schedule['jour'] }}</td>
                            <td>{{ $schedule['concours'] }}</td>
                            <td>{{ $schedule['horaire'] }}</td>
                            <td>{{ $schedule['duree'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="important">
            <h4>IMPORTANT :</h4>
            <ul>
                <li>Présentez-vous <strong>30 minutes avant l'heure de début</strong></li>
                <li>Apportez <strong>cette convocation</strong> et votre <strong>pièce d'identité</strong></li>
                <li>Respectez les consignes de sécurité et les règles du concours</li>
                <li>Tout retard pourra entraîner l'exclusion du concours</li>
            </ul>
        </div>

        <p><strong>Consignes générales :</strong></p>
        <ul>
            <li>Arrivez à l'avance pour les formalités d'identification</li>
            <li>Apportez le matériel autorisé selon le type d'épreuve</li>
            <li>Respectez le silence pendant l'épreuve</li>
            <li>Ne quittez pas la salle sans autorisation</li>
        </ul>
    </div>

    <div class="signature">
        <p>Fait à la Faculté des Sciences et Techniques de Marrakech, le {{ date('d/m/Y') }}</p>
        <div class="signature-line"></div>
        <p>Signature et cachet</p>
    </div>

</body>

</html>
