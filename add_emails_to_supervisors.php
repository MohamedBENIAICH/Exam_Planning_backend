<?php

/**
 * Script pour ajouter des emails aux superviseurs qui n'en ont pas
 */

require_once 'vendor/autoload.php';

use App\Models\Superviseur;
use Illuminate\Support\Facades\DB;

echo "=== Ajout d'emails aux superviseurs ===\n\n";

try {
    // Récupérer tous les superviseurs sans email
    $superviseursSansEmail = Superviseur::whereNull('email')->get();

    echo "Nombre de superviseurs sans email : " . $superviseursSansEmail->count() . "\n\n";

    if ($superviseursSansEmail->count() === 0) {
        echo "✅ Tous les superviseurs ont déjà des emails.\n";
        exit(0);
    }

    // Ajouter des emails aux superviseurs
    foreach ($superviseursSansEmail as $index => $superviseur) {
        $email = strtolower($superviseur->prenom . '.' . $superviseur->nom . '@fst.ma');

        // Nettoyer l'email (enlever les accents et caractères spéciaux)
        $email = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ô', 'ö', 'ù', 'û', 'ü', 'ç', 'ñ', ' ', '-', "'", '"'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'o', 'o', 'u', 'u', 'u', 'c', 'n', '.', '.', '', ''],
            $email
        );

        // Mettre à jour le superviseur
        $superviseur->email = $email;
        $superviseur->save();

        echo "✅ Email ajouté pour {$superviseur->prenom} {$superviseur->nom} : {$email}\n";
    }

    echo "\n=== Résumé ===\n";
    echo "✅ " . $superviseursSansEmail->count() . " emails ajoutés avec succès.\n";
    echo "✅ Tous les superviseurs ont maintenant des emails.\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
