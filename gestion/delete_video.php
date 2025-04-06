<?php
// delete_video.php
session_start(); // Démarre ou reprend une session existante

require __DIR__ . '/../connexion/msql.php'; // Chemin vers la connexion à la base de données

// Vérifier si un ID est passé en paramètre GET
if (isset($_GET['id'])) {
    $video_id = intval($_GET['id']); // Convertir l'ID en entier pour éviter les injections SQL

    try {
        // Vérifier si la vidéo existe
        $stmt_check = $conn->prepare("SELECT id FROM videos WHERE id = ?");
        $stmt_check->bind_param("i", $video_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        // Si la vidéo n'existe pas, lance une exception
        if ($result->num_rows === 0) {
            throw new Exception("Vidéo introuvable");
        }

        // Supprimer la vidéo
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $video_id);

        // Si la suppression réussit, enregistre un message de succès dans la session, sinon lance une exception
        if ($stmt->execute()) {
            $_SESSION['message'] = "Vidéo supprimée avec succès";
        } else {
            throw new Exception("Erreur lors de la suppression");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
    } finally {
        // Libérer les ressources et rediriger
        if (isset($stmt_check)) $stmt_check->close(); // Ferme la requête de vérification si elle a été exécutée
        if (isset($stmt)) $stmt->close(); // Ferme la requête de suppression si elle a été exécutée
        header("Location: dashboard.php"); // Redirection après suppression
        exit(); // Termine l'exécution du script
    }
} else {
    $_SESSION['error'] = "Aucun ID vidéo spécifié"; // Enregistre un message d'erreur dans la session si aucun ID n'est spécifié
    header("Location: dashboard.php"); // Redirection vers le tableau de bord
    exit(); // Termine l'exécution du script
}
?>
