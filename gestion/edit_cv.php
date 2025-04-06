<?php
session_start(); // Démarre ou reprend une session existante

require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

// Récupère l'ID du CV depuis les paramètres GET
$cv_id = $_GET['id'] ?? null;

// Vérifie si l'ID du CV est présent
if (!$cv_id) {
    $_SESSION['error'] = "ID de CV non spécifié"; // Enregistre un message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}

// Récupère les données du CV existant
$query = "SELECT * FROM cv WHERE id = ?";
$stmt = $conn->prepare($query); // Prépare la requête SQL
$stmt->bind_param("i", $cv_id); // Lie l'ID du CV à la requête préparée
$stmt->execute(); // Exécute la requête
$cv = $stmt->get_result()->fetch_assoc(); // Récupère le résultat de la requête et le stocke dans un tableau associatif

// Vérifie si le CV a été trouvé
if (!$cv) {
    $_SESSION['error'] = "CV non trouvé"; // Enregistre un message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}

// Traitement du formulaire si la méthode est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Récupération et nettoyage des données du formulaire
        $nom_cv = htmlspecialchars($_POST['nom_cv']); // Nom du CV
        $description = htmlspecialchars($_POST['description']); // Description du CV
        $actif = isset($_POST['actif']) ? 1 : 0; // Statut du CV (actif ou inactif)

        // Mise à jour des données du CV
        $stmt = $conn->prepare("UPDATE cv SET nom_cv = ?, description = ?, actif = ? WHERE id = ?"); // Prépare la requête SQL
        $stmt->bind_param("sssi", $nom_cv, $description, $actif, $cv_id); // Lie les paramètres à la requête préparée

        // Exécution de la requête
        if (!$stmt->execute()) {
            throw new Exception("Erreur base de données: " . $stmt->error); // Lance une exception en cas d'erreur lors de l'exécution de la requête
        }

        $_SESSION['message'] = "CV mis à jour avec succès"; // Enregistre un message de succès dans la session
        $stmt->close(); // Ferme la requête préparée

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
    } finally {
        $conn->close(); // Ferme la connexion à la base de données
    }

    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le CV</title>
    <!-- Ajout d'un favicon pour améliorer l'identité visuelle -->
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <!-- Entête -->
            <div class="p-6 border-b border-purple-200">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800">
                    <i class="fas fa-file-edit mr-2"></i>Édition du CV
                </h2>
            </div>

            <!-- Formulaire d'édition -->
            <div class="p-8 space-y-6">
                <!-- Affichage des messages de succès ou d'erreur -->
                <?php if(isset($_SESSION['message'])): ?>
                    <!-- Message de succès -->
                    <div class="p-4 text-green-800 bg-green-100 rounded-lg">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php unset($_SESSION['message']); // Suppression du message de la session ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <!-- Message d'erreur -->
                    <div class="p-4 text-red-800 bg-red-100 rounded-lg">
                        <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); // Suppression du message de la session ?>
                <?php endif; ?>

                <!-- Formulaire de modification du CV -->
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <!-- Label pour le nom du CV -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du CV *</label>
                                <!-- Input pour le nom du CV -->
                                <input type="text" name="nom_cv" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       value="<?= htmlspecialchars($cv['nom_cv']) ?>">
                            </div>

                            <div>
                                <!-- Label pour le fichier CV -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fichier CV</label>
                                <div class="relative">
                                    <!-- Input pour le fichier CV -->
                                    <input type="file" name="fileToUpload"
                                           accept=".pdf,.docx"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                                    <!-- Indication des formats acceptés -->
                                    <span class="text-xs text-gray-400 mt-2 block">Formats acceptés : PDF, DOCX (max 5MB)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <div>
                                <!-- Label pour la description -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <!-- Textarea pour la description -->
                                <textarea name="description" rows="4"
                                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($cv['description']) ?></textarea>
                            </div>

                            <!-- Checkbox pour la visibilité publique -->
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="actif" id="actif"
                                       class="h-4 w-4 text-purple-600 rounded focus:ring-purple-500 border-gray-300" <?= $cv['actif'] ? 'checked' : '' ?>>
                                <!-- Label pour le checkbox -->
                                <label for="actif" class="text-sm text-gray-700">CV visible publiquement</label>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton pour mettre à jour le CV -->
                        <button type="submit"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Mettre à jour le CV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
