<?php
// Démarrage de la session pour gérer les messages utilisateur
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../connexion/msql.php';

// Vérification des droits d'accès admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header('Location: ../login.php');
    exit();
}

// Traitement uniquement pour les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Début de transaction pour atomicité des opérations
        $conn->begin_transaction();

        // Configuration des sections principales du formulaire
        $sections = [
            'header_title', 'header_subtitle',         // En-tête
            'intro_title', 'intro_text',               // Introduction
            'first_title', 'first_item1_title', 'first_item1_text', // Première section
            'first_item2_title', 'first_item2_text',
            'first_item3_title', 'first_item3_text',
            'second_title', 'second_text', 'second_content', // Seconde section
            'second_button_text', 'second_button_link',// Bouton
            'cta_title', 'cta_text',                   // CTA
            'about_title', 'about_text',               // À propos
            'projects_title', 'gallery_title'          // Projets et galerie
        ];

        // Ajout dynamique des champs statistiques (5 éléments)
        for ($i = 1; $i <= 5; $i++) {
            array_push($sections, 
                "second_stat{$i}_icon",    // Icône Font Awesome
                "second_stat{$i}_number",  // Valeur numérique
                "second_stat{$i}_label"    // Libellé texte
            );
        }

        // Préparation de la requête unique pour toutes les mises à jour
        $stmt = $conn->prepare("UPDATE home_content SET content = ? WHERE section_name = ?");

        // Parcours de toutes les sections définies
        foreach ($sections as $section) {
            // Récupération sécurisée de la valeur avec valeur par défaut
            $value = $_POST[$section] ?? '';
            
            // Liaison des paramètres : valeur et nom de section
            $stmt->bind_param('ss', $value, $section);
            
            // Exécution avec gestion d'erreur détaillée
            if (!$stmt->execute()) {
                throw new Exception("Erreur sur $section : " . $stmt->error);
            }
        }

        // Validation globale si tout est OK
        $conn->commit();
        $_SESSION['message'] = "Mise à jour réussie !";

    } catch (Exception $e) {
        // Annulation en cas d'erreur et stockage du message
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    } finally {
        // Nettoyage des ressources
        $stmt->close();
        $conn->close();
        // Redirection quel que soit le résultat
        header('Location: dashboard.php');
        exit();
    }
}
?>