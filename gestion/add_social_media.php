<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

// Vérification de la session
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

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
    
    // Redirection vers la même page pour afficher le message
    
    header("Location: add_social_media.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un média social</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-hashtag mr-2"></i>Ajouter un réseau social
                </h2>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form action="add_social_media.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nom du média -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du réseau *</label>
                            <input type="text" name="nom" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Ex: LinkedIn, GitHub...">
                        </div>

                        <!-- Lien -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lien du profil *</label>
                            <input type="url" name="link" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="https://...">
                        </div>
                    </div>

                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>Ajouter le réseau
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
