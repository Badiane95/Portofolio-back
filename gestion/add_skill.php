<?php
session_start(); // Démarre ou reprend une session existante

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header("Location: login.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas administrateur
    exit(); // Termine l'exécution du script
}

require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Traitement du formulaire si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire pour éviter les injections SQL
    $icon = htmlspecialchars($_POST['icon']); // Nom de l'icône Font Awesome
    $title = htmlspecialchars($_POST['title']); // Titre de la compétence
    $description = htmlspecialchars($_POST['description']); // Description de la compétence

    // Préparation de la requête SQL pour insérer les données dans la table "skills"
    $stmt = $conn->prepare("INSERT INTO skills (icon, title, description) VALUES (?, ?, ?)");
    
    // Liaison des paramètres à la requête préparée
    $stmt->bind_param("sss", $icon, $title, $description);

    // Exécution de la requête
    $stmt->execute();
    
    $_SESSION['message'] = "Compétence ajoutée !"; // Enregistre un message de succès dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit(); // Termine l'exécution du script
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Compétence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>

    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-plus-circle mr-2"></i>Ajouter une Compétence
                </h2>
                
                <!-- Formulaire pour ajouter une nouvelle compétence -->
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Icône -->
                        <div>
                            <!-- Label pour l'icône -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icône Font Awesome</label>
                            <!-- Input pour l'icône -->
                            <input type="text" name="icon" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="code (sans le 'fa-')">
                        </div>

                        <!-- Titre -->
                        <div>
                            <!-- Label pour le titre -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <!-- Input pour le titre -->
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Description -->
                        <div>
                            <!-- Label pour la description -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <!-- Textarea pour la description -->
                            <textarea name="description" rows="4" required
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                        </div>
                    </div>

                    <!-- Section pour le bouton "Enregistrer" -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton pour soumettre le formulaire et enregistrer la compétence -->
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
