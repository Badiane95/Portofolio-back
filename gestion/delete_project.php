<?php
session_start(); // Démarre ou reprend une session existante

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification de la présence de l'ID dans les paramètres GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Récupère l'ID et le convertit en entier pour des raisons de sécurité
    
    try {
        // Récupération du chemin de l'image avant suppression
        $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id); // Lie l'ID à la requête préparée
        $stmt->execute(); // Exécute la requête
        $result = $stmt->get_result(); // Récupère le résultat de la requête
        $project = $result->fetch_assoc(); // Récupère la ligne de résultat sous forme de tableau associatif
        
        // Vérification si le projet existe
        if ($project) {
            // Suppression du projet
            $delete_stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
            $delete_stmt->bind_param("i", $id); // Lie l'ID à la requête préparée
            
            // Exécution de la requête de suppression
            if ($delete_stmt->execute()) {
                // Suppression du fichier image
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($project['image_path'], PHP_URL_PATH); // Reconstitue le chemin complet de l'image
                
                // Vérification de l'existence du fichier image
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Supprime le fichier image
                }
                
                $_SESSION['message'] = "Le projet a été supprimé avec succès."; // Enregistre un message de succès dans la session
            } else {
                throw new Exception("Erreur lors de la suppression : " . $delete_stmt->error); // Lance une exception en cas d'erreur lors de la suppression du projet
            }
            $delete_stmt->close(); // Fermeture de la requête de suppression
        } else {
            throw new Exception("Projet introuvable"); // Lance une exception si le projet n'est pas trouvé
        }
        
        $stmt->close(); // Fermeture de la requête de récupération du chemin de l'image
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
    }
    
    header("Location: dashboard.php"); // Redirection vers le tableau de bord
    exit; // Termine l'exécution du script
}

$conn->close(); // Fermeture de la connexion à la base de données
?>
