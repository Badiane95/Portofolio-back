<?php
session_start();

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Récupération du chemin de l'image avant suppression
        $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        
        if ($project) {
            // Suppression du projet
            $delete_stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
            $delete_stmt->bind_param("i", $id);
            
            if ($delete_stmt->execute()) {
                // Suppression du fichier image
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . parse_url($project['image_path'], PHP_URL_PATH);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                $_SESSION['message'] = "Le projet a été supprimé avec succès.";
            } else {
                throw new Exception("Erreur lors de la suppression : " . $delete_stmt->error);
            }
            $delete_stmt->close();
        } else {
            throw new Exception("Projet introuvable");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>