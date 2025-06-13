<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Rendu Concours - {{ $concours->titre }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .logo {
            max-width: 300px;
            margin: 0 auto 15px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }

        .header h2 {
            color: #34495e;
            margin: 10px 0 0 0;
            font-size: 18px;
        }

        .concours-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }

        .concours-info h3 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 16px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #2c3e50;
        }

        .info-value {
            color: #34495e;
        }

        .section {
            margin-top: 30px;
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: #3498db;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .empty-message {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #ddd;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
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
        <h1>COMPTE RENDU DE CONCOURS</h1>
        <h2>Titre:{{ $concours->titre }}</h2>
        <p>Description:{{ $concours->description }}</p>
    </div>

    <div class="concours-info">
        <h3>INFORMATIONS DU CONCOURS</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Date du concours:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($concours->date_concours)->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Heure:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($concours->heure_debut)->format('H:i') }} -
                    {{ \Carbon\Carbon::parse($concours->heure_fin)->format('H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Locaux:</span>
                <span class="info-value">{{ $concours->locaux }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Type d'épreuve:</span>
                <span class="info-value">{{ $concours->type_epreuve }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">PROFESSEURS ASSIGNÉS</div>
        @if ($concours->professeurs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concours->professeurs as $professeur)
                        <tr>
                            <td>{{ $professeur->nom }}</td>
                            <td>{{ $professeur->prenom }}</td>
                            <td>{{ $professeur->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                Aucun professeur assigné.
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">SUPERVISEURS ASSIGNÉS</div>
        @if ($concours->superviseurs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concours->superviseurs as $superviseur)
                        <tr>
                            <td>{{ $superviseur->nom }}</td>
                            <td>{{ $superviseur->prenom }}</td>
                            <td>{{ $superviseur->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                Aucun superviseur assigné.
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">CANDIDATS CONVOQUÉS ({{ $concours->candidats->count() }})</div>
        @if ($concours->candidats->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>CNE</th>
                        <th>CIN</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concours->candidats as $candidat)
                        <tr>
                            <td>{{ $candidat->CNE }}</td>
                            <td>{{ $candidat->CIN }}</td>
                            <td>{{ $candidat->nom }}</td>
                            <td>{{ $candidat->prenom }}</td>
                            <td>{{ $candidat->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-message">
                Aucun candidat assigné.
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Fait à Marrakech le {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
        <p>&copy; Faculté des Sciences et Techniques de Marrakech - Système de Gestion des Concours</p>
    </div>
</body>

</html>
