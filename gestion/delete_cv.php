<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Requête pour obtenir le chemin du fichier avant de le supprimer
    $selectQuery = $conn->prepare("SELECT chemin_cv FROM cv WHERE id = ?");
    $selectQuery->bind_param("i", $id);
    $selectQuery->execute();
    $selectQuery->bind_result($filePath);
    $selectQuery->fetch();
    $selectQuery->close();

    if ($filePath) {
        // Chemin complet du fichier à supprimer
        $fullFilePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;

        // Suppression du fichier physique
        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }
    }

    // Suppression de l'entrée dans la base de données
    $deleteQuery = $conn->prepare("DELETE FROM cv WHERE id = ?");
    $deleteQuery->bind_param("i", $id);

    if ($deleteQuery->execute()) {
        $_SESSION['message'] = "Le CV a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $deleteQuery->error;
    }

    $deleteQuery->close();
    header("Location: dashboard.php");
    exit;
} else {
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>
