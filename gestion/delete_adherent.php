<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $query = $conn->prepare("DELETE FROM adherents WHERE id = ?");
        $query->bind_param("i", $id);

        if ($query->execute()) {
            $_SESSION['message'] = "L'adhérent a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $query->error;
        }

        $query->close();
        header("Location: dashboard.php");
        exit;
    } else {
        // Afficher une confirmation avant la suppression
        echo "<script>
            if (confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) {
                window.location.href = 'delete_adherent.php?id=$id&confirm=yes';
            } else {
                window.location.href = 'dashboard.php';
            }
        </script>";
    }
} else {
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>
