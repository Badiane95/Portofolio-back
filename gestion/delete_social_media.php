<?php
session_start(); // Démarre ou reprend une session existante

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification de la présence de l'ID dans les paramètres GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Récupère l'ID et le convertit en entier pour des raisons de sécurité

    // Validation de l'ID
    if ($id <= 0) {
        $_SESSION['error'] = "ID invalide."; // Enregistre un message d'erreur dans la session
        header("Location: dashboard.php"); // Redirige vers le tableau de bord
        exit; // Termine l'exécution du script
    }
    
    // Préparation de la requête SQL pour supprimer le média social
    $stmt = $conn->prepare("DELETE FROM social_media WHERE id = ?");
    $stmt->bind_param("i", $id); // Lie l'ID à la requête préparée

    // Initialisation des variables pour stocker le message de succès ou d'erreur
    $message = "";
    $error = "";
    
    // Exécution de la requête
    if ($stmt->execute()) {
        $message = "Média social supprimé avec succès."; // Message de succès
    } else {
        $error = "Erreur de suppression: " . $stmt->error; // Message d'erreur
    }
    $stmt->close(); // Fermeture de la requête préparée

    // Redirection vers dashboard.php après le traitement avec les messages appropriés
    $_SESSION['message'] = $message; // Enregistre le message de succès dans la session
    $_SESSION['error'] = $error; // Enregistre le message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
} else {
    $_SESSION['error'] = "ID non spécifié."; // Enregistre un message d'erreur dans la session si l'ID n'est pas spécifié
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de suppression</title>
    <!-- Style CSS pour l'overlay de confirmation -->
    <style>
        .confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none; /* Masqué par défaut */
        }

        .confirmation-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        /* Animation de fondu */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
    <!-- Script JavaScript pour la confirmation de suppression -->
    <script>
        function confirmDeletion(id) {
            // Affiche l'overlay de confirmation
            document.getElementById('confirmation-overlay').style.display = 'flex';

            // Ajoute les gestionnaires d'événements aux boutons
            document.getElementById('confirm-button').onclick = function() {
                window.location.href = 'delete_social_media.php?id=' + id; // Redirige vers la page de suppression si l'utilisateur confirme
            };

            document.getElementById('cancel-button').onclick = function() {
                document.getElementById('confirmation-overlay').style.display = 'none'; // Masque l'overlay si l'utilisateur annule
            };
        }
    </script>
</head>
<body>
    <!-- Overlay de confirmation -->
    <div class="confirmation-overlay" id="confirmation-overlay">
        <div class="confirmation-box fade-in">
            <h2>Confirmation</h2>
            <p>Êtes-vous sûr de vouloir supprimer ce média social ?</p>
            <!-- Bouton de confirmation de suppression -->
            <button id="confirm-button">Oui, supprimer</button>
            <!-- Bouton d'annulation -->
            <button id="cancel-button">Annuler</button>
        </div>
    </div>

    <!-- Exemple d'utilisation (Ajouter à votre tableau de bord ou à votre liste) -->
    <!-- Lien de suppression avec appel à la fonction de confirmation -->
    <a href="#" onclick="confirmDeletion(<?php echo $social_media_id; ?>)">Supprimer</a>
</body>
</html>
