<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation à l'examen</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
        }

        .container {
            max-width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            background-color: white;
            padding: 2cm 1.5cm;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 300px;
            margin: 0 auto 15px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .date {
            text-align: left;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .title-section {
            text-align: center;
            margin: 20px 0;
        }

        h1 {
            color: #000;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        h2 {
            font-size: 20px;
            margin: 10px 0;
        }

        .content {
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .student-info {
            margin: 25px 0;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-row strong {
            display: inline-block;
            width: 120px;
        }

        .qr-code {
            position: absolute;
            right: 2cm;
            top: 12cm;
        }

        .qr-code img {
            width: 100px;
            height: auto;
        }

        .exam-schedule {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .exam-schedule th,
        .exam-schedule td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        .exam-schedule th {
            background-color: #f2f2f2;
        }

        .exam-schedule .green {
            background-color: #a0daa9;
        }

        .exam-schedule .blue {
            background-color: #a0c4da;
        }

        .exam-schedule .yellow {
            background-color: #dacea0;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 14px;
            line-height: 1.5;
        }

        .stamp {
            position: absolute;
            right: 2cm;
            bottom: 5cm;
            width: 120px;
            height: auto;
        }

        .stamp img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('images/logofst.jpeg') }}" alt="Logo de la faculté">
            </div>
        </div>

        <div class="date">
            Marrakech, le {{ date('d/m/Y') }}
        </div>

        <div class="title-section">
            <h1>Convocation</h1>
            <h2>Aux 2<sup>èmes</sup> contrôles</h2>
            <h2>Session de {{ $exam['session'] }} {{ $exam['year'] }}</h2>
        </div>

        <div class="content">
            <div class="student-info">
                <div class="info-row">
                    <strong>Nom :</strong> {{ $student->prenom }}
                </div>
                <div class="info-row">
                    <strong>Prénom :</strong> {{ $student->nom }}
                </div>
                <div class="info-row">
                    <strong>Code Apogée :</strong> {{ $student->numero_etudiant ?? '' }}
                </div>
                <div class="info-row">
                    <strong>CNE :</strong> {{ $student->cne ?? '' }}
                </div>
                <div class="info-row">
                    <strong>Local :</strong> {{ $exam['salle'] }}
                </div>
            </div>

            <div class="qr-code">
                <img src="{{ public_path('storage/qrcodes/' . basename($qrCodePath)) }}" alt="QR Code">
            </div>

            <p>Vous êtes convoqué(e) aux 2èmes contrôles de la Session de {{ $exam['session'] }} {{ $exam['year'] }},
                dont le programme est arrêté comme suit :</p>

            <table class="exam-schedule">
                <thead>
                    <tr>
                        <th colspan="5">Semestre {{ $exam['semestre'] }}</th>
                    </tr>
                    <tr>
                        <th>{{ $exam['month'] }} {{ $exam['year'] }}</th>
                        <th>Modules</th>
                        <th>Durée</th>
                        <th>Horaire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($examSchedule as $schedule)
                        <tr>
                            <td>{{ $schedule['jour'] }}</td>
                            <td class="{{ $schedule['color'] }}">{{ $schedule['module'] }}</td>
                            <td>{{ $schedule['duree'] }}</td>
                            <td>{{ $schedule['horaire'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer-text">
                <p>Vous êtes tenu(e) de vous présenter au local indiqué une demi-heure avant chaque épreuve, muni(e) de
                    la présente convocation, de votre carte d'étudiant et de votre pièce d'identité.</p>
            </div>

            <div class="stamp">
                <img src="{{ public_path('images/cachet.png') }}" alt="Tampon officiel">
            </div>
        </div>
    </div>
</body>

</html>
