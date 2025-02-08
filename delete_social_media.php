<?php
session_start();
include 'msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM social_media WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Média social supprimé";
    } else {
        $_SESSION['error'] = "Erreur de suppression";
    }
    $stmt->close();
}

header("Location: dashboard.php");
exit;
?>
