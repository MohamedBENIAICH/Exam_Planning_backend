<!DOCTYPE html>
<html>

<head>
    <title>Convocation pour la Surveillance d'Examen</title>
</head>

<body>
    <p>Bonjour {{ $surveillantName }},</p>

    <p>Vous avez été sélectionné(e) pour surveiller l'examen suivant :</p>

    <ul>
        <li><strong>Module :</strong> {{ is_object($exam->module) ? $exam->module->module_intitule : $exam->module }}
        </li>
        <li><strong>Date :</strong> {{ \Carbon\Carbon::parse($exam->date_examen)->format('d/m/Y') }}</li>
        <li><strong>Heure de début :</strong> {{ \Carbon\Carbon::parse($exam->heure_debut)->format('H:i') }}</li>
        <li><strong>Heure de fin :</strong> {{ \Carbon\Carbon::parse($exam->heure_fin)->format('H:i') }}</li>
        <li><strong>Salles :</strong> {{ implode(', ', $exam->classrooms->pluck('nom_du_local')->toArray()) }}</li>
    </ul>

    <p>Merci de votre collaboration.</p>

    <p>Cordialement,<br>
        Administration FST</p>
</body>

</html>
