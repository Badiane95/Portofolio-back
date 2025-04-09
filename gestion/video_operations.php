<?php
// Démarrage de la session pour gérer les messages utilisateur
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../connexion/msql.php';

// Traitement de l'ajout de vidéo
if(isset($_POST['add_video'])) {
    // Nettoyage des données avec real_escape_string (protection basique contre les injections SQL)
    $title = $conn->real_escape_string($_POST['title']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $description = $conn->real_escape_string($_POST['description']);

    // Requête préparée pour plus de sécurité
    $stmt = $conn->prepare("INSERT INTO videos (title, video_url, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $video_url, $description);
    
    // Exécution avec gestion des erreurs
    if($stmt->execute()) {
        $_SESSION['message'] = "Vidéo ajoutée avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout: " . $stmt->error;
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Traitement de la suppression de vidéo
if(isset($_GET['delete'])) {
    // Validation du paramètre ID
    $id = intval($_GET['delete']);
    
    // Requête préparée pour éviter les injections SQL
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

// Traitement de la modification de vidéo
if(isset($_POST['update_video'])) {
    // Récupération et validation des données
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $description = $conn->real_escape_string($_POST['description']);

    // Requête préparée pour la mise à jour
    $stmt = $conn->prepare("UPDATE videos SET title=?, video_url=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $video_url, $description, $id);
    
    if($stmt->execute()) {
        $_SESSION['message'] = "Vidéo mise à jour avec succès!";
    } else {
        $_SESSION['error'] = "Erreur de mise à jour: " . $stmt->error;
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}
