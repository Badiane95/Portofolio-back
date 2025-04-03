<?php
session_start();

// Vérification d'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/../connexion/msql.php';

// Ajout de vidéo
if(isset($_POST['add_video'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $description = $conn->real_escape_string($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO videos (title, video_url, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $video_url, $description);
    
    if($stmt->execute()) {
        $_SESSION['message'] = "Vidéo ajoutée avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout: " . $stmt->error;
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Suppression de vidéo
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION['message'] = "Vidéo supprimée avec succès!";
    } else {
        $_SESSION['error'] = "Erreur de suppression: " . $stmt->error;
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Modification de vidéo
if(isset($_POST['update_video'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $description = $conn->real_escape_string($_POST['description']);

    $stmt = $conn->prepare("UPDATE videos SET title=?, video_url=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $video_url, $description, $id);
    
    if($stmt->execute()) {
        $_SESSION['message'] = "Vidéo mise à jour avec succès!";
    } else {
        $_SESSION['error'] = "Erreur de mise à jour: " . $stmt->error;
    }
    $stmt->close();
    header("Location: dashboard.php"); // Redirection corrigée
    exit();
}