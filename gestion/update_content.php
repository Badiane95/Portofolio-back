<?php
// Démarrage de la session pour gérer l'authentification et les messages
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../connexion/msql.php';

// Vérification des droits administrateur
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit();
}

// Récupération du contenu existant pour la page d'accueil
$query_home_content = "SELECT * FROM home_content";
$result_home_content = $conn->query($query_home_content);
$home_data = [];

// Construction du tableau associatif des données existantes
while ($row = $result_home_content->fetch_assoc()) {
    // Mappage général des sections
    $home_data[$row['section_name']] = $row['content'];
    
    
}

// Traitement du formulaire en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Début de transaction pour la sécurité des opérations
        $conn->begin_transaction();

        // Liste des sections modifiables
        $sections = [
            'header_title', 'header_subtitle', 'intro_title', 'intro_text',
            'first_title',
            'first_item1_icon', 'first_item1_title', 'first_item1_text',
            'first_item2_icon', 'first_item2_title', 'first_item2_text',
            'first_item3_icon', 'first_item3_title', 'first_item3_text',
            'second_title', 'second_text', 'second_content',
            'second_button_text', 'second_button_link', 'cta_title', 'cta_text',
            'about_title', 'about_text', 'projects_title', 'gallery_title'
        ];

        // Ajout dynamique des statistiques (5 éléments numérotés)
        for ($i = 1; $i <= 5; $i++) {
            $sections[] = "second_stat{$i}_icon";
            $sections[] = "second_stat{$i}_number";
            $sections[] = "second_stat{$i}_label";
        }

      

        // Préparation de la requête préparée
        $stmt = $conn->prepare("UPDATE home_content SET content = ? WHERE section_name = ?");

        // Parcours de toutes les sections à mettre à jour
        foreach ($sections as $section) {
            if (isset($_POST[$section])) {
                // Nettoyage et récupération de la valeur
                $value = $_POST[$section];
                
                // Liaison des paramètres
                $stmt->bind_param('ss', $value, $section);

                // Exécution avec gestion d'erreur
                if (!$stmt->execute()) {
                    throw new Exception("Erreur de mise à jour pour $section: " . $stmt->error);
                }
            }
        }

        // Validation globale des modifications
        $conn->commit();
        $_SESSION['message'] = "Mise à jour réussie !";

    } catch (Exception $e) {
        // Annulation en cas d'erreur
        $conn->rollback();
        $_SESSION['error'] = "Erreur critique : " . $e->getMessage();
    } finally {
        // Nettoyage des ressources
        if (isset($stmt)) $stmt->close();
        if (isset($conn)) $conn->close();
        
        // Redirection avec feedback utilisateur
        header('Location: dashboard.php');
        exit();
    }
}
?>
