<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

$imageId = $_GET['id'] ?? null;

// Vérifiez si un ID d'image est fourni
if (!$imageId) {
    die("ID d'image non fourni.");
}

// Récupérez les détails de l'image
$stmt = $conn->prepare("SELECT * FROM images WHERE id = ?");
$stmt->bind_param("i", $imageId);
$stmt->execute();
$image = $stmt->get_result()->fetch_assoc();

if (!$image) {
    die("Image non trouvée.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newTitle = $_POST['title'] ?? '';

    // Préparez la requête SQL pour mettre à jour le titre de l'image
    $updateStmt = $conn->prepare("UPDATE images SET title = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newTitle, $imageId);

    if ($updateStmt->execute()) {
        echo "Nom de l'image mis à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour du nom de l'image.";
    }
    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le nom de l'image</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-edit mr-2"></i>Modifier le nom de l'image
                </h1>

                <form action="" method="post" class="space-y-6">
                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau titre *</label>
                        <input type="text" name="title" required
                               value="<?= htmlspecialchars($image['title']) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Nouveau titre">
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
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
