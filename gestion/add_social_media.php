<?php
session_start(); // Démarre ou reprend une session existante

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification de la session pour s'assurer que l'utilisateur est un administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit; // Termine l'exécution du script
}

// Traitement du formulaire si la méthode est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données du formulaire pour éviter les injections SQL
    $nom = $conn->real_escape_string($_POST['nom']); // Nom du réseau social
    $link = $conn->real_escape_string($_POST['link']); // Lien vers le profil du réseau social

    // Préparation de la requête SQL pour insérer les données dans la table "social_media"
    $stmt = $conn->prepare("INSERT INTO social_media (nom, link) VALUES (?, ?)");
    
    // Liaison des paramètres à la requête préparée
    $stmt->bind_param("ss", $nom, $link);
    
    // Exécution de la requête
    if ($stmt->execute()) {
        $_SESSION['message'] = "Média social ajouté avec succès"; // Enregistre un message de succès dans la session
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout"; // Enregistre un message d'erreur dans la session
    }
    $stmt->close(); // Ferme la requête préparée
    
    // Redirection vers la même page pour afficher le message
    
    header("Location: add_social_media.php"); // Redirige vers la page d'ajout de média social
    exit; // Termine l'exécution du script
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un média social</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de SweetAlert2 pour des alertes plus esthétiques (non utilisé ici) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Ajout d'un favicon pour améliorer l'identité visuelle -->
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>
    
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-hashtag mr-2"></i>Ajouter un réseau social
                </h2>

                <!-- Affichage des messages de succès ou d'erreur -->
                <?php if(isset($_SESSION['message'])): ?>
                    <!-- Message de succès -->
                    <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); // Suppression du message de la session ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <!-- Message d'erreur -->
                    <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); // Suppression du message de la session ?>
                <?php endif; ?>

                <!-- Formulaire d'ajout de média social -->
                <form action="add_social_media.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Nom du média -->
                        <div>
                            <!-- Label pour le nom du média -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du réseau *</label>
                            <!-- Input pour le nom du média -->
                            <input type="text" name="nom" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Ex: LinkedIn, GitHub...">
                        </div>

                        <!-- Lien -->
                        <div>
                            <!-- Label pour le lien -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lien du profil *</label>
                            <!-- Input pour le lien -->
                            <input type="url" name="link" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="https://...">
                        </div>
                    </div>

                    <!-- Section pour le bouton d'ajout -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton de soumission du formulaire -->
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
