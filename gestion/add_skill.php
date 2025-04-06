<?php
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/../connexion/msql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = htmlspecialchars($_POST['icon']);
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO skills (icon, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $icon, $title, $description);
    $stmt->execute();
    
    $_SESSION['message'] = "Compétence ajoutée !";
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Compétence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-plus-circle mr-2"></i>Ajouter une Compétence
                </h2>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Icône -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icône Font Awesome</label>
                            <input type="text" name="icon" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="code (sans le 'fa-')">
                        </div>

                        <!-- Titre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4" required
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                        </div>
                    </div>

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