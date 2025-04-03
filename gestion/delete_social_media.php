<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Validate the ID
    if ($id <= 0) {
        $_SESSION['error'] = "ID invalide.";
        header("Location: dashboard.php");
        exit;
    }
    
    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM social_media WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Store success/error in a variable before redirecting
    $message = "";
    $error = "";
    
    if ($stmt->execute()) {
        $message = "Média social supprimé avec succès.";
    } else {
        $error = "Erreur de suppression: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to dashboard.php after processing with appropriate messages
    $_SESSION['message'] = $message;
    $_SESSION['error'] = $error;
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['error'] = "ID non spécifié.";
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Deletion Confirmation</title>
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
            display: none;
        }

        .confirmation-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        /* Basic animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
    <script>
        function confirmDeletion(id) {
            // Show the confirmation overlay
            document.getElementById('confirmation-overlay').style.display = 'flex';

            // Attach event listeners to the buttons
            document.getElementById('confirm-button').onclick = function() {
                window.location.href = 'delete_social_media.php?id=' + id;
            };

            document.getElementById('cancel-button').onclick = function() {
                document.getElementById('confirmation-overlay').style.display = 'none';
            };
        }
    </script>
</head>
<body>
    <!-- Confirmation Overlay -->
    <div class="confirmation-overlay" id="confirmation-overlay">
        <div class="confirmation-box fade-in">
            <h2>Confirmation</h2>
            <p>Êtes-vous sûr de vouloir supprimer ce média social ?</p>
            <button id="confirm-button">Oui, supprimer</button>
            <button id="cancel-button">Annuler</button>
        </div>
    </div>

    <!-- Example Usage (Add to your Dashboard or List) -->
    <a href="#" onclick="confirmDeletion(<?php echo $social_media_id; ?>)">Supprimer</a>
</body>
</html>
