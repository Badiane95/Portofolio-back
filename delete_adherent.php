<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}
include 'msql.php'; 

if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $id = $_GET['id'];

    $query = $conn->prepare("DELETE FROM adherents WHERE id = ?");
    $query->bind_param("i", $id);

    if ($query->execute()) {
        $_SESSION['message'] = "L'utilisateur a été supprimé avec succès.";
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $query->error;
        header("Location: dashboard.php");
        exit;
    }

    $query->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un utilisateur</title>
    <meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
       <script src="script.js"></script>
		<noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <script>
        function confirmDelete(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
                window.location.href = "delete_adherent.php?id=" + id + "&confirm=yes";
            } else {
                window.location.href = "dashboard.php";
            }
        }
    </script>
</head>
<body>
    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        echo "<script>confirmDelete($id);</script>";
    }
    ?>
</body>
</html>
