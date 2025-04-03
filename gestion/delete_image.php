<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

        $query = $conn->prepare("DELETE FROM images WHERE id = ?");
        $query->bind_param("i", $id);

        if ($query->execute()) {
            $_SESSION['message'] = "L'image a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $query->error;
        }

        $query->close();
        header("Location: dashboard.php");
        exit;
} else {
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>


