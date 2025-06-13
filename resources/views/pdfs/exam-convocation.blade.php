<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte rendu Examen</title>
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

        .exam-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }

        .exam-info h3 {
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

        .students-section {
            margin-top: 30px;
        }

        .students-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .students-table th {
            background-color: #3498db;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }

        .students-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .students-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .students-table tr:hover {
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

        .supervisors-section {
            margin-top: 30px;
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
        }

        .supervisors-section h4 {
            color: #2c3e50;
            margin-top: 0;
        }

        .supervisor-list {
            margin: 10px 0;
        }

        .supervisor-item {
            margin: 5px 0;
            color: #34495e;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }

            .page-break {
                page-break-before: always;
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
        <h2>COMPTE RENDU EXAMEN</h2>
    </div>

    <div class="exam-info">
        <h3>INFORMATIONS DE L'EXAMEN</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Formation:</span>
                <span class="info-value">{{ $formation ? $formation->formation_intitule : 'Non spécifiée' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Filière:</span>
                <span class="info-value">{{ $filiere ? $filiere->filiere_intitule : 'Non spécifiée' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Module:</span>
                <span class="info-value">{{ $module ? $module->module_intitule : 'Non spécifié' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Semestre:</span>
                <span class="info-value">{{ $exam->semestre }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $date_examen }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Heure:</span>
                <span class="info-value">{{ $heure_debut }} - {{ $heure_fin }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Locaux:</span>
                <span
                    class="info-value">{{ $exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée' }}</span>
            </div>
        </div>
    </div>

    @if ($superviseurs && $superviseurs->count() > 0)
        <div class="supervisors-section">
            <h4>SUPERVISEURS ASSIGNÉS</h4>
            <div class="supervisor-list">
                @foreach ($superviseurs as $superviseur)
                    <div class="supervisor-item">
                        • {{ $superviseur->nom }} {{ $superviseur->prenom }}
                        @if ($superviseur->email)
                            ({{ $superviseur->email }})
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($professeurs && $professeurs->count() > 0)
        <div class="supervisors-section">
            <h4>PROFESSEURS ASSIGNÉS</h4>
            <div class="supervisor-list">
                @foreach ($professeurs as $professeur)
                    <div class="supervisor-item">
                        • {{ $professeur->nom }} {{ $professeur->prenom }}
                        @if ($professeur->email)
                            ({{ $professeur->email }})
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="students-section">
        <h3>LISTE DES ÉTUDIANTS CONVOQUÉS</h3>
        <p><strong>Nombre total d'étudiants: {{ $students->count() }}</strong></p>

        @if ($students->count() > 0)
            <table class="students-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>CNE</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Niveau</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->cne }}</td>
                            <td>{{ $student->nom }}</td>
                            <td>{{ $student->prenom }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->niveau }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">
                Aucun étudiant n'est convoqué pour cet examen.
            </p>
        @endif
    </div>

    <div class="footer">
        <p>Fait à Marrakech le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
        <p>&copy; Faculté des Sciences et Techniques de Marrakech- Système de Gestion des Examens</p>
    </div>
</body>

</html>
