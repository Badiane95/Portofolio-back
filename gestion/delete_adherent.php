<?php
session_start(); // Démarre ou reprend une session existante

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si un ID d'adhérent est passé en paramètre GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Récupère l'ID et le convertit en entier pour des raisons de sécurité

    // Vérifie si la suppression est confirmée
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        // Prépare la requête SQL pour supprimer l'adhérent
        $query = $conn->prepare("DELETE FROM adherents WHERE id = ?");
        $query->bind_param("i", $id); // Lie l'ID à la requête préparée

        // Exécute la requête
        if ($query->execute()) {
            $_SESSION['message'] = "L'adhérent a été supprimé avec succès."; // Enregistre un message de succès dans la session
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $query->error; // Enregistre un message d'erreur dans la session
        }

        $query->close(); // Ferme la requête préparée
        header("Location: dashboard.php"); // Redirige vers le tableau de bord
        exit; // Termine l'exécution du script
    } else {
        // Afficher une confirmation avant la suppression en utilisant JavaScript
        echo "<script>
            if (confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?')) {
                window.location.href = 'delete_adherent.php?id=$id&confirm=yes'; // Redirige vers la page de suppression avec confirmation
            } else {
                window.location.href = 'dashboard.php'; // Redirige vers le tableau de bord si l'utilisateur annule
            }
        </script>";
    }
} else {
    header("Location: dashboard.php"); // Redirige vers le tableau de bord si aucun ID n'est fourni
    exit; // Termine l'exécution du script
}

$conn->close(); // Ferme la connexion à la base de données
?>
