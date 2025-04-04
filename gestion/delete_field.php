<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

// Vérifier l'existence du paramètre et des droits
if (isset($_POST['id']) && !empty($_SESSION['admin'])) {
    $id = intval($_POST['id']);
    
    try {
        // Requête préparée pour la suppression
        $stmt = $conn->prepare("DELETE FROM contact_form WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Vérifier si une ligne a été affectée
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "✅ Champ supprimé avec succès";
            } else {
                $_SESSION['error'] = "⚠️ Aucun champ trouvé avec cet ID";
            }
        } else {
            throw new Exception("Erreur d'exécution : " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "🚨 Erreur de suppression : " . $e->getMessage();
    } finally {
        $stmt->close();
        $conn->close();
    }
} else {
    $_SESSION['error'] = "🔒 Action non autorisée";
}

header("Location: dashboard.php");
exit();
?>