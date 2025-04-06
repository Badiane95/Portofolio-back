<?php
session_start(); // Démarre ou reprend une session existante

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si un ID est passé en paramètre GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Récupère l'ID et le convertit en entier pour des raisons de sécurité

    // Requête pour obtenir le chemin du fichier avant de le supprimer
    $selectQuery = $conn->prepare("SELECT chemin_cv FROM cv WHERE id = ?");
    $selectQuery->bind_param("i", $id); // Lie l'ID à la requête préparée
    $selectQuery->execute(); // Exécute la requête
    $selectQuery->bind_result($filePath); // Lie le résultat (chemin du fichier) à la variable $filePath
    $selectQuery->fetch(); // Récupère le résultat
    $selectQuery->close(); // Ferme la requête préparée

    // Vérifie si un chemin de fichier a été trouvé
    if ($filePath) {
        // Chemin complet du fichier à supprimer
        $fullFilePath = $_SERVER['DOCUMENT_ROOT'] . $filePath; // Concatène le chemin racine du serveur avec le chemin relatif du fichier

        // Suppression du fichier physique si il existe
        if (file_exists($fullFilePath)) {
            unlink($fullFilePath); // Supprime le fichier
        }
    }

    // Suppression de l'entrée dans la base de données
    $deleteQuery = $conn->prepare("DELETE FROM cv WHERE id = ?");
    $deleteQuery->bind_param("i", $id); // Lie l'ID à la requête préparée

    // Exécute la requête
    if ($deleteQuery->execute()) {
        $_SESSION['message'] = "Le CV a été supprimé avec succès."; // Enregistre un message de succès dans la session
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $deleteQuery->error; // Enregistre un message d'erreur dans la session
    }

    $deleteQuery->close(); // Ferme la requête préparée
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
} else {
    header("Location: dashboard.php"); // Redirige vers le tableau de bord si aucun ID n'est fourni
    exit; // Termine l'exécution du script
}

$conn->close(); // Ferme la connexion à la base de données
?>
