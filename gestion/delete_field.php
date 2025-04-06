<?php
session_start(); // Démarre ou reprend une session existante

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'ID est présent dans les données POST
if (isset($_POST['id']) && !empty($_SESSION['admin'])) {
    $id = intval($_POST['id']); // Récupère l'ID et le convertit en entier pour des raisons de sécurité

    try {
        // Prépare la requête SQL pour supprimer le champ
        $stmt = $conn->prepare("DELETE FROM contact_form WHERE id = ?");
        $stmt->bind_param("i", $id); // Lie l'ID à la requête préparée

        // Exécute la requête
        if ($stmt->execute()) {
            // Vérifie si une ligne a été affectée par la requête DELETE
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "✅ Champ supprimé avec succès"; // Enregistre un message de succès dans la session
            } else {
                $_SESSION['error'] = "⚠️ Aucun champ trouvé avec cet ID"; // Enregistre un message d'erreur dans la session si aucun champ n'a été trouvé avec cet ID
            }
        } else {
            throw new Exception("Erreur d'exécution : " . $stmt->error); // Lance une exception en cas d'erreur d'exécution de la requête
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "🚨 Erreur de suppression : " . $e->getMessage(); // Enregistre un message d'erreur dans la session en cas d'exception
    } finally {
        $stmt->close(); // Ferme la requête préparée
        $conn->close(); // Ferme la connexion à la base de données
    }
} else {
    $_SESSION['error'] = "🔒 Action non autorisée"; // Enregistre un message d'erreur dans la session si l'action n'est pas autorisée
}

header("Location: dashboard.php"); // Redirige vers le tableau de bord
exit(); // Termine l'exécution du script
?>
