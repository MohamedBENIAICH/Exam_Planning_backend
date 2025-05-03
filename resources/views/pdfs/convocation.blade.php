<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Convocation à l'examen</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1a3c5e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            border-radius: 10px;
        }

        .logo-placeholder {
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .title-section {
            text-align: center;
            padding: 10px 20px;
            margin: 20px 0;
            position: relative;
        }

        h1 {
            color: #1a3c5e;
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .title-section:after {
            content: '';
            display: block;
            height: 3px;
            width: 100px;
            background: #d4af37;
            margin: 10px auto 0;
        }

        .content {
            margin-bottom: 30px;
            line-height: 1.6;
        }

        p {
            margin-bottom: 15px;
        }

        .exam-details {
            background-color: #f5f9ff;
            border-left: 4px solid #1a3c5e;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .student-details {
            background-color: #f0f7f0;
            border-left: 4px solid #2e7d32;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .qr-code {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
            color: #666;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .important-note {
            font-weight: bold;
            color: #c62828;
        }

        .details-item {
            display: flex;
            margin-bottom: 8px;
        }

        .details-label {
            font-weight: bold;
            width: 130px;
            color: #555;
        }

        .details-value {
            flex: 1;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-placeholder">
                    <img src="{{ asset('../images/uca.jpeg') }}" alt="Logo de l'université">
                </div>
            </div>
            <div class="logo">
                <div class="logo-placeholder">
                    <img src="{{ asset('../images/logo fst.jpeg') }}" alt="Logo de la faculté">
                </div>
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
                    <div class="details-label">CNE :</div>
                    <div class="details-value">{{ $student->cne }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Nom :</div>
                    <div class="details-value">{{ $student->nom }}</div>
                </div>
                <div class="details-item">
                    <div class="details-label">Prénom :</div>
                    <div class="details-value">{{ $student->prenom }}</div>
                </div>
            </div>
        </div>

        <div class="qr-code">
            <img src="{{ $qrCodePath }}" alt="QR Code" style="width: 200px;">
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
