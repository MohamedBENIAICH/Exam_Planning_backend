<!DOCTYPE html>
<html>

<head>
    <title>Convocation pour la Surveillance d'Examen</title>
</head>

<body>
    <p>Bonjour {{ $surveillantName }},</p>

    <p>Vous avez été sélectionné(e) pour surveiller l'examen suivant :</p>

    <ul>
        <li><strong>Module :</strong> {{ $exam->module ? $exam->module->module_intitule : 'Module non spécifié' }}</li>
        <li><strong>Date :</strong>
            {{ $exam->date_examen ? $exam->date_examen->format('d/m/Y') : 'Date non spécifiée' }}</li>
        <li><strong>Heure de début :</strong>
            {{ $exam->heure_debut ? $exam->heure_debut->format('H:i') : 'Heure non spécifiée' }}</li>
        <li><strong>Heure de fin :</strong>
            {{ $exam->heure_fin ? $exam->heure_fin->format('H:i') : 'Heure non spécifiée' }}</li>
        <li><strong>Salles :</strong>
            {{ $exam->classrooms->pluck('nom_du_local')->implode(', ') ?: 'Salle non spécifiée' }}</li>
    </ul>

    <p>Merci de votre collaboration.</p>

    <p>Cordialement,<br>
        Administration FSTG</p>
</body>

</html>
