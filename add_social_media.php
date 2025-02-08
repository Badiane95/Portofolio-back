<?php
session_start();
include 'msql.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $conn->real_escape_string($_POST['nom']);
    $link = $conn->real_escape_string($_POST['link']);

    $stmt = $conn->prepare("INSERT INTO social_media (nom, link) VALUES (?, ?)");
    $stmt->bind_param("ss", $nom, $link);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Média social ajouté avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout";
    }
    $stmt->close();
}

header("Location: dashboard.php");
exit;
?>
