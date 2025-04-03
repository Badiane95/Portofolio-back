<?php
// delete_video.php
session_start();
require __DIR__ . '/../connexion/msql.php';

// Vérifier les permissions utilisateur
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $video_id = intval($_GET['id']);
    
    try {
        // Vérifier l'existence de la vidéo
        $stmt_check = $conn->prepare("SELECT id FROM videos WHERE id = ?");
        $stmt_check->bind_param("i", $video_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Vidéo introuvable");
        }
        
        // Suppression sécurisée
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $video_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Vidéo supprimée avec succès";
            // Audit log
            $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, target) VALUES (?, ?, ?)");
            $log_action = "DELETE_VIDEO";
            $log_stmt->bind_param("iss", $_SESSION['user_id'], $log_action, $video_id);
            $log_stmt->execute();
        } else {
            throw new Exception("Erreur de suppression");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
} else {
    $_SESSION['error'] = "ID vidéo non spécifié";
    header("Location: dashboard.php");
    exit();
}
?>
