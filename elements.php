<?php
// elements.php - Correction du chemin de connexion
include __DIR__ . '/connexion/msql.php';

$result = $conn->query("SELECT * FROM skills ORDER BY created_at DESC");
$skills = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Compétences</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-violet-50">
    <div class="container mx-auto px-4 py-12">
        <h2 class="text-4xl font-bold text-center text-violet-800 mb-12">Mes Compétences</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($skills as $skill): ?>
                <div class="bg-white rounded-lg shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-xl border-2 border-violet-200">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-violet-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-<?= htmlspecialchars($skill['icon']) ?> text-3xl text-violet-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-violet-800 mb-3"><?= htmlspecialchars($skill['title']) ?></h3>
                        <p class="text-gray-600"><?= htmlspecialchars($skill['description']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="container mx-auto px-4 py-4">
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                <i class="fas fa-home mr-2"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>