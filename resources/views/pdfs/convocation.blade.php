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
            font-family: 'Segoe UI', Arial, sans-serif;
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
            padding: 1.5cm 1.5cm;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1a3c5e;
            padding-bottom: 15px;
            margin-bottom: 15px;
            width: 100%;
        }

        .logo-left {
            width: 80px;
            height: 80px;
            align-self: flex-start;
        }

        .logo-right {
            width: 80px;
            height: 80px;
            align-self: flex-end;
        }

        .logo-left img,
        .logo-right img {
            max-width: 100%;
            max-height: 100%;
        }

        .title-section {
            text-align: center;
            padding: 5px 0;
            margin: 10px 0;
        }

        h1 {
            color: #1a3c5e;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .title-section:after {
            content: '';
            display: block;
            height: 3px;
            width: 80px;
            background: #d4af37;
            margin: 8px auto 0;
        }

        .content {
            margin-bottom: 15px;
            line-height: 1.4;
        }

        p {
            margin-bottom: 10px;
            font-size: 14px;
        }

        h3 {
            margin: 5px 0;
            font-size: 16px;
            color: #1a3c5e;
        }

        .exam-details {
            background-color: #f5f9ff;
            border-left: 4px solid #1a3c5e;
            padding: 8px 12px;
            margin: 12px 0;
        }

        .student-details {
            background-color: #f0f7f0;
            border-left: 4px solid #2e7d32;
            padding: 8px 12px;
            margin: 12px 0;
        }

        .qr-code {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
        }

        .qr-code img {
            width: 120px;
            height: auto;
        }

        .qr-code p {
            margin: 5px 0 0;
            font-size: 12px;
        }

        .footer {
            position: absolute;
            bottom: 1.5cm;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 11px;
            color: #666;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .important-note {
            font-weight: bold;
            color: #c62828;
        }

        .details-item {
            display: flex;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .details-label {
            font-weight: bold;
            width: 110px;
            color: #555;
        }

        .details-value {
            flex: 1;
        }

        .signature {
            text-align: right;
            font-size: 13px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo-left">
                <img src="{{ public_path('images/uca.jpeg') }}" alt="Logo de l'université">
            </div>
            <div class="logo-right">
                <img src="{{ public_path('images/logofst.jpeg') }}" alt="Logo de la faculté">
            </div>
        </div>

        <div class="title-section">
            <h1>Convocation à l'examen</h1>
        </div>

        <div class="content">
            <p>Cher(e) étudiant(e),</p>

            <p>Nous avons le plaisir de vous convoquer à l'examen dont les détails sont mentionnés ci-dessous. Veuillez
                vous présenter à l'heure indiquée muni(e) de cette convocation et de votre carte d'étudiant.</p>

            <div class="exam-details">
                <h3>Détails de l'examen</h3>
                <div class="details-item">
                    <div class="details-label">Module :</div>
                    <div class="details-value">{{ $exam['name'] }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Date :</div>
                    <div class="details-value">{{ $exam['date'] }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Heure de début :</div>
                    <div class="details-value">{{ $exam['heure_debut'] }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Heure de fin :</div>
                    <div class="details-value">{{ $exam['heure_fin'] }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Salle :</div>
                    <div class="details-value">{{ $exam['salle'] }}</div>
                </div>
            </div>

            <div class="student-details">
                <h3>Informations de l'étudiant</h3>
                <div class="details-item">
                    <div class="details-label">Code Apogée :</div>
                    <div class="details-value">
                        {{ $student->cne ?? ($student->numero_etudiant ?? 'Non renseigné') }}
                    </div>
                </div>
                <div class="details-item">
                    <div class="details-label">Nom :</div>
                    <div class="details-value">{{ $student->prenom }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Prénom :</div>
                    <div class="details-value">{{ $student->nom }}</div>
                </div>
            </div>
        </div>

        <div class="qr-code">
            <img src="{{ public_path('storage/qrcodes/' . basename($qrCodePath)) }}" alt="QR Code">
            <p>Veuillez scanner ce QR code à l'entrée de la salle d'examen</p>
        </div>

        <div class="signature">
            <p>Le service des examens</p>
        </div>

        <div class="footer">
            <p class="important-note">Veuillez vous présenter 20 minutes avant le début de l'examen.</p>
            <p>Présentez cette convocation imprimée ainsi que votre carte d'étudiant le jour de l'examen.</p>
            <p>L'usage des téléphones portables et autres appareils électroniques est strictement interdit durant
                l'examen.</p>
        </div>
    </div>
</body>

</html>
