<?php
session_start(); // Démarre ou reprend une session existante

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

require __DIR__ . '/../connexion/msql.php'; // Utilisation de require pour une connexion essentielle

// Vérification des paramètres GET
if (isset($_GET['delete']) && isset($_GET['id'])) {
    // Validation de l'ID
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Filtre et valide l'ID pour s'assurer qu'il s'agit d'un entier

    // Vérification si l'ID est valide
    if (!$id || $id <= 0) {
        $_SESSION['error'] = "ID invalide"; // Enregistre un message d'erreur dans la session
        header('Location: dashboard.php'); // Redirige vers le tableau de bord
        exit(); // Termine l'exécution du script
    }

    try {
        // Vérification de la connexion
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error); // Lance une exception si la connexion à la base de données échoue
        }

        // Préparation et exécution de la requête
        $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?"); // Prépare la requête SQL pour supprimer la compétence
        $stmt->bind_param("i", $id); // Lie l'ID à la requête préparée

        // Vérification si l'exécution de la requête réussit
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête"); // Lance une exception si l'exécution de la requête échoue
        }

        // Vérification du succès de l'opération
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Compétence supprimée avec succès !"; // Enregistre un message de succès dans la session
        } else {
            $_SESSION['error'] = "Aucune compétence trouvée avec cet ID"; // Enregistre un message d'erreur dans la session si aucune compétence n'est trouvée avec cet ID
        }

    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage(); // Enregistre un message d'erreur dans la session en cas d'erreur de base de données
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre un message d'erreur dans la session en cas d'autre exception
    } finally {
        // Fermeture des ressources
        if (isset($stmt)) $stmt->close(); // Ferme la requête préparée si elle a été créée
        if ($conn) $conn->close(); // Ferme la connexion à la base de données si elle a été établie
    }

} else {
    $_SESSION['error'] = "Paramètres manquants pour la suppression"; // Enregistre un message d'erreur dans la session si des paramètres sont manquants
}

// Redirection vers la page de gestion
header('Location: dashboard.php'); // Redirige vers le tableau de bord
exit(); // Termine l'exécution du script
?>
