<?php
// delete_video.php
session_start();
require __DIR__ . '/../connexion/msql.php'; // Chemin vers la connexion à la base de données


// Vérifier si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $video_id = intval($_GET['id']); // Convertir l'ID en entier pour éviter les injections SQL

    try {
        // Vérifier si la vidéo existe
        $stmt_check = $conn->prepare("SELECT id FROM videos WHERE id = ?");
        $stmt_check->bind_param("i", $video_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Vidéo introuvable");
        }

        // Supprimer la vidéo
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $video_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Vidéo supprimée avec succès";
        } else {
            throw new Exception("Erreur lors de la suppression");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        // Libérer les ressources et rediriger
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        header("Location: dashboard.php"); // Redirection après suppression
        exit();
    }
} else {
    $_SESSION['error'] = "Aucun ID vidéo spécifié";
    header("Location: dashboard.php");
    exit();
}
?>
