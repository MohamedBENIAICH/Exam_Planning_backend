# Implémentation des QR Codes pour les Concours

## Vue d'ensemble

Cette implémentation ajoute la génération de QR codes dans les PDFs de convocation pour les concours, similaire à la fonctionnalité existante pour les examens. **De plus, elle inclut l'envoi automatique des convocations mises à jour avec QR codes lors de la modification d'un concours, avec une vue spécifique pour distinguer les mises à jour des créations initiales.**

## Format du QR Code

Le QR code contient les informations suivantes au format JSON :

```json
{
    "nom": "ABAHRI",
    "prenom": "Hatim",
    "CNE": "2320382",
    "CIN": "570161"
}
```

## Modifications apportées

### 1. Service ConcoursNotificationService

**Fichier :** `app/Services/ConcoursNotificationService.php`

-   Ajout de l'injection de dépendance du `QRCodeService`
-   Modification de la méthode `generateConvocationPDF()` pour générer un QR code pour chaque candidat
-   **Nouveau :** Ajout de la méthode `generateUpdatedConvocationPDF()` spécifique pour les mises à jour
-   Le QR code est généré avec les données du candidat (nom, prénom, CNE, CIN)
-   Gestion d'erreur si la génération du QR code échoue

### 2. Contrôleur ConcoursController

**Fichier :** `app/Http/Controllers/ConcoursController.php`

-   **Nouveau :** Ajout de l'envoi automatique des convocations mises à jour avec QR codes lors de la modification d'un concours
-   La méthode `update()` appelle maintenant automatiquement `sendUpdatedConvocations()` après la mise à jour
-   Gestion d'erreur et logging pour le suivi des envois

### 3. Templates PDF de convocation

**Fichier :** `resources/views/pdfs/concours-convocation.blade.php`

-   Vue originale pour les créations de concours
-   Ajout du QR code dans la section des informations du candidat
-   Le QR code est affiché avec une taille de 100x100 pixels
-   Ajout d'un texte explicatif sous le QR code

**Fichier :** `resources/views/pdfs/concours-convocation-updated.blade.php` (NOUVEAU)

-   **Vue spécifique pour les mises à jour** avec design distinctif
-   Couleurs orange (#ff6b35) pour indiquer une mise à jour
-   Bannière d'avertissement "CONVOCATION MISE À JOUR"
-   Messages spécifiques pour les modifications
-   QR code avec bordure orange pour indiquer qu'il a été mis à jour
-   Section "IMPORTANT - MODIFICATIONS APPORTÉES"

### 4. Classes Mail

**Fichier :** `app/Mail/ConcoursConvocation.php`

-   Classe Mail originale pour les créations de concours
-   Ajout d'une mention du QR code dans le message de l'email
-   Transmission du chemin du QR code au template d'email

**Fichier :** `app/Mail/ConcoursConvocationUpdated.php` (NOUVEAU)

-   **Classe Mail spécifique pour les mises à jour**
-   Sujet : "Convocation mise à jour - Concours"
-   Messages adaptés au contexte de mise à jour
-   Nom de fichier attaché : "convocation_concours_mise_a_jour.pdf"

### 5. Templates Email

**Fichier :** `resources/views/emails/concours-convocation.blade.php`

-   Template email original pour les créations
-   Ajout d'une mention du QR code dans les consignes importantes

**Fichier :** `resources/views/emails/concours-convocation-updated.blade.php` (NOUVEAU)

-   **Template email spécifique pour les mises à jour**
-   Design distinctif avec couleurs orange
-   Bannière d'avertissement "CONVOCATION MISE À JOUR"
-   Section spéciale "QR Code mis à jour"
-   Messages spécifiques pour les modifications

### 6. Interface utilisateur

**Fichiers :** `src/components/Dashboard/UpcomingConcours.tsx` et `src/pages/ConcourScheduling.tsx`

-   **Nouveau :** Messages de confirmation mis à jour pour informer l'utilisateur que les convocations mises à jour avec QR codes ont été envoyées automatiquement

## Fonctionnalités

### Génération automatique

-   Le QR code est généré automatiquement lors de la création d'un concours
-   Le QR code est également généré lors de l'envoi de convocations mises à jour

### **Envoi automatique lors de la modification**

-   **Nouveau :** Lors de la modification d'un concours, les convocations mises à jour avec QR codes sont automatiquement envoyées aux candidats
-   **Nouveau :** Utilisation d'une vue et d'un email spécifiques pour les mises à jour
-   Les notifications de surveillance et de mise à jour sont également envoyées aux superviseurs et professeurs
-   Gestion complète des erreurs avec logging

### **Distinction visuelle entre création et mise à jour**

-   **Création :** Vue bleue avec design standard
-   **Mise à jour :** Vue orange avec design distinctif et messages d'avertissement
-   QR codes avec bordures différentes (bleue vs orange)
-   Messages et consignes adaptés au contexte

### Stockage

-   Les QR codes sont stockés dans `storage/app/public/qrcodes/`
-   Chaque QR code a un nom unique généré avec `uniqid()`

### Gestion d'erreur

-   Si la génération du QR code échoue, le PDF est généré sans QR code
-   Les erreurs sont loggées pour le débogage

## Utilisation

La fonctionnalité est automatiquement activée lors de :

1. La création d'un nouveau concours (vue et email standards)
2. **La modification d'un concours existant** (vue et email spécifiques pour les mises à jour)
3. L'envoi de convocations mises à jour
4. L'envoi de notifications de surveillance

## Test

Des tests ont été effectués avec succès :

-   **Test de génération de QR code :** Candidat de test : ABAHRI Hatim (CNE: 2320382, CIN: 570161) - PDF généré avec succès (34,521 bytes) incluant le QR code
-   **Test d'envoi automatique lors de la modification :** Envoi de convocations mises à jour avec QR codes réussi
-   **Test de la vue de mise à jour :** Vue spécifique testée avec succès (38,186 bytes) avec design distinctif

## Compatibilité

Cette implémentation est compatible avec la logique existante et n'affecte pas les fonctionnalités actuelles des examens ou des concours.

## Avantages de l'envoi automatique avec vue spécifique

1. **Cohérence des données :** Les candidats reçoivent toujours les informations les plus récentes
2. **QR codes à jour :** Les QR codes sont régénérés avec les nouvelles informations
3. **Transparence :** L'utilisateur est informé que les convocations ont été mises à jour automatiquement
4. **Fiabilité :** Gestion d'erreur robuste pour éviter les interruptions du processus de modification
5. **Distinction claire :** Les candidats peuvent facilement identifier les convocations mises à jour grâce au design distinctif
6. **Messages adaptés :** Les consignes et messages sont spécifiquement adaptés au contexte de mise à jour
