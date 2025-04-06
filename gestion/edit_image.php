<?php
session_start(); // Démarre ou reprend une session existante

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

$imageId = $_GET['id'] ?? null; // Récupère l'ID de l'image depuis les paramètres GET

// Vérifie si un ID d'image est fourni
if (!$imageId) {
    die("ID d'image non fourni."); // Affiche un message d'erreur et termine l'exécution du script si aucun ID n'est fourni
}

// Récupère les détails de l'image
$stmt = $conn->prepare("SELECT * FROM images WHERE id = ?"); // Prépare la requête SQL
$stmt->bind_param("i", $imageId); // Lie l'ID de l'image à la requête préparée
$stmt->execute(); // Exécute la requête
$image = $stmt->get_result()->fetch_assoc(); // Récupère le résultat de la requête

// Vérifie si l'image a été trouvée
if (!$image) {
    die("Image non trouvée."); // Affiche un message d'erreur et termine l'exécution du script si l'image n'est pas trouvée
}

// Traitement du formulaire si la méthode est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newTitle = $_POST['title'] ?? ''; // Récupère le nouveau titre de l'image depuis les données POST

    // Prépare la requête SQL pour mettre à jour le titre de l'image
    $updateStmt = $conn->prepare("UPDATE images SET title = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newTitle, $imageId); // Lie le nouveau titre et l'ID de l'image à la requête préparée

    // Exécute la requête
    if ($updateStmt->execute()) {
        echo "Nom de l'image mis à jour avec succès."; // Affiche un message de succès si la mise à jour réussit
    } else {
        echo "Erreur lors de la mise à jour du nom de l'image."; // Affiche un message d'erreur si la mise à jour échoue
    }
    $updateStmt->close(); // Ferme la requête préparée
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le nom de l'image</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h1 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-edit mr-2"></i>Modifier le nom de l'image
                </h1>

                <!-- Formulaire de modification du nom de l'image -->
                <form action="" method="post" class="space-y-6">
                    <!-- Titre -->
                    <div>
                        <!-- Label pour le titre -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau titre *</label>
                        <!-- Input pour le titre -->
                        <input type="text" name="title" required
                               value="<?= htmlspecialchars($image['title']) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Nouveau titre">
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton pour enregistrer les modifications -->
                        <button type="submit"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
