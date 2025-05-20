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
            <li><strong>Code Apogée :</strong> {{ $student->numero_etudiant }}</li>
            <li><strong>Nom :</strong> {{ $student->prenom }}</li>
            <li><strong>Prénom :</strong> {{ $student->nom }}</li>
        </ul>
    </div>
    <div class="footer">
        <p>Vous trouverez ci-joint le pdf contenant votre convocation.Veuillez présenter cette convocation et votre
            carte d'étudiant le jour de l'examen.</p>
        <p>Merci de votre compréhension.</p>
    </div>
</body>

</html>
