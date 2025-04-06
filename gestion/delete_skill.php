<?php
session_start();

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php");
    exit;
}

require __DIR__ . '/../connexion/msql.php'; // Utilisation de require pour une connexion essentielle

// Vérification des paramètres GET
if (isset($_GET['delete']) && isset($_GET['id'])) {
    // Validation de l'ID
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if (!$id || $id <= 0) {
        $_SESSION['error'] = "ID invalide";
        header('Location: dashboard.php');
        exit();
    }

    try {
        // Vérification de la connexion
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Préparation et exécution de la requête
        $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
        
        // Vérification du succès de l'opération
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Compétence supprimée avec succès !";
        } else {
            $_SESSION['error'] = "Aucune compétence trouvée avec cet ID";
        }
        
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        if (isset($stmt)) $stmt->close();
        if ($conn) $conn->close();
    }
    
} else {
    $_SESSION['error'] = "Paramètres manquants pour la suppression";
}

// Redirection vers la page de gestion
header('Location: dashboard.php');
exit();
?>