<?php
session_start(); // Démarre ou reprend une session existante

// Sécurité des en-têtes
header("X-Frame-Options: DENY"); // Empêche le site d'être intégré dans un iframe (protection contre le clickjacking)
header("X-Content-Type-Options: nosniff"); // Empêche le navigateur de déterminer le type MIME à partir du contenu (renforce la sécurité)
header("X-XSS-Protection: 1; mode=block"); // Active la protection XSS du navigateur et bloque les attaques détectées

// Vérification des droits admin
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: login.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Génération du token CSRF (Cross-Site Request Forgery)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token aléatoire et le stocke dans la session
}

// Fonctions de validation et de nettoyage
function sanitizeIcon($input) {
    $clean = preg_replace('/[^a-z-]/i', '', trim($input)); // Supprime tous les caractères non alphanumériques et les espaces
    if (empty($clean)) {
        throw new Exception("Format d'icône invalide"); // Lance une exception si le format de l'icône est invalide
    }
    return $clean; // Retourne la valeur nettoyée
}

function sanitizeText($input, $maxLength, $fieldName) {
    $clean = strip_tags(trim($input)); // Supprime les balises HTML et les espaces
    if (empty($clean)) {
        throw new Exception("Le champ '$fieldName' est requis"); // Lance une exception si le champ est vide
    }
    if (strlen($clean) > $maxLength) {
        throw new Exception("Le champ '$fieldName' ne doit pas dépasser $maxLength caractères"); // Lance une exception si la longueur du champ dépasse la limite
    }
    return $clean; // Retourne la valeur nettoyée
}

// Traitement GET (récupération des données de la compétence à modifier)
$skill = []; // Initialise un tableau vide pour stocker les données de la compétence
if (isset($_GET['id'])) {
    try {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Filtre et valide l'ID pour s'assurer qu'il s'agit d'un entier

        // Vérification si l'ID est valide
        if (!$id || $id <= 0) {
            throw new Exception("ID invalide"); // Lance une exception si l'ID n'est pas valide
        }

        $stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?"); // Prépare la requête SQL pour récupérer la compétence
        $stmt->bind_param("i", $id); // Lie l'ID à la requête préparée
        $stmt->execute(); // Exécute la requête
        $skill = $stmt->get_result()->fetch_assoc(); // Récupère les données de la compétence

        // Vérification si la compétence a été trouvée
        if (!$skill) {
            throw new Exception("Compétence introuvable"); // Lance une exception si la compétence n'est pas trouvée
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
        header('Location: dashboard.php'); // Redirige vers le tableau de bord
        exit(); // Termine l'exécution du script
    }
}

// Traitement POST (mise à jour de la compétence)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Token de sécurité invalide"); // Lance une exception si le token CSRF est invalide
        }

        // Validation ID
        if (empty($_POST['id'])) {
            throw new Exception("ID manquant"); // Lance une exception si l'ID est manquant
        }
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT); // Filtre et valide l'ID pour s'assurer qu'il s'agit d'un entier
        if (!$id || $id <= 0) {
            throw new Exception("ID invalide"); // Lance une exception si l'ID n'est pas valide
        }

        // Nettoyage des entrées
        $icon = sanitizeIcon($_POST['icon']); // Nettoie l'icône
        $title = sanitizeText($_POST['title'], 100, 'titre'); // Nettoie le titre
        $description = sanitizeText($_POST['description'], 255, 'description'); // Nettoie la description

        // Mise à jour base de données
        $stmt = $conn->prepare("UPDATE skills SET icon=?, title=?, description=? WHERE id=?"); // Prépare la requête SQL pour mettre à jour la compétence
        $stmt->bind_param("sssi", $icon, $title, $description, $id); // Lie les paramètres à la requête préparée

        // Vérification de l'exécution de la requête
        if (!$stmt->execute()) {
            throw new Exception("Échec de la mise à jour : " . $stmt->error); // Lance une exception si la mise à jour échoue
        }

        $_SESSION['message'] = "Compétence mise à jour avec succès !"; // Enregistre un message de succès dans la session
        header("Location: dashboard.php"); // Redirige vers le tableau de bord
        exit(); // Termine l'exécution du script

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
        header("Location: edit_skill.php?id=" . ($_POST['id'] ?? '')); // Redirige vers la page de modification de la compétence
        exit(); // Termine l'exécution du script
    }
}

$conn->close(); // Ferme la connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Compétence</title>
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
                    <i class="fas fa-pen-to-square mr-2"></i>Modifier la compétence
                </h2>

                <!-- Affichage du message d'erreur -->
                <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 p-4 mb-6 rounded-lg border border-red-200">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); // Suppression du message d'erreur de la session ?>
                </div>
                <?php endif; ?>

                <!-- Formulaire de modification de la compétence -->
                <form method="POST" class="space-y-6">
                    <!-- Champ caché pour l'ID de la compétence -->
                    <input type="hidden" name="id" value="<?= htmlspecialchars($skill['id'] ?? '') ?>">
                    <!-- Champ caché pour le token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Icône -->
                        <div>
                            <!-- Label pour l'icône -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icône Font Awesome</label>
                            <!-- Input pour l'icône -->
                            <input type="text" name="icon"
                                   value="<?= htmlspecialchars($skill['icon'] ?? '', ENT_QUOTES) ?>"
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="code (sans le 'fa-')">
                            <!-- Indication du format de l'icône -->
                            <p class="text-sm text-gray-500 mt-1">Ex: code pour &lt;i class="fas fa-code"&gt;</p>
                        </div>

                        <!-- Titre -->
                        <div>
                            <!-- Label pour le titre -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <!-- Input pour le titre -->
                            <input type="text" name="title"
                                   value="<?= htmlspecialchars($skill['title'] ?? '', ENT_QUOTES) ?>"
                                   required
                                   maxlength="100"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <!-- Label pour la description -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <!-- Textarea pour la description -->
                        <textarea name="description" rows="4" required
                                  maxlength="255"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($skill['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <!-- Lien Annuler -->
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                            <!-- Bouton Enregistrer -->
                            <button type="submit"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
