<?php
session_start();

// Headers de sécurité
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Vérification des droits admin
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit;
}

require __DIR__ . '/../connexion/msql.php';

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fonctions de validation
function sanitizeIcon($input) {
    $clean = preg_replace('/[^a-z-]/i', '', trim($input));
    if (empty($clean)) {
        throw new Exception("Format d'icône invalide");
    }
    return $clean;
}

function sanitizeText($input, $maxLength, $fieldName) {
    $clean = strip_tags(trim($input));
    if (empty($clean)) {
        throw new Exception("Le champ '$fieldName' est requis");
    }
    if (strlen($clean) > $maxLength) {
        throw new Exception("Le champ '$fieldName' ne doit pas dépasser $maxLength caractères");
    }
    return $clean;
}

// Traitement GET
$skill = [];
if (isset($_GET['id'])) {
    try {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        
        if (!$id || $id <= 0) {
            throw new Exception("ID invalide");
        }

        $stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $skill = $stmt->get_result()->fetch_assoc();
        
        if (!$skill) {
            throw new Exception("Compétence introuvable");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Token de sécurité invalide");
        }

        // Validation ID
        if (empty($_POST['id'])) {
            throw new Exception("ID manquant");
        }
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            throw new Exception("ID invalide");
        }

        // Nettoyage des entrées
        $icon = sanitizeIcon($_POST['icon']);
        $title = sanitizeText($_POST['title'], 100, 'titre');
        $description = sanitizeText($_POST['description'], 255, 'description');

        // Mise à jour base de données
        $stmt = $conn->prepare("UPDATE skills SET icon=?, title=?, description=? WHERE id=?");
        $stmt->bind_param("sssi", $icon, $title, $description, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Échec de la mise à jour : " . $stmt->error);
        }
        
        $_SESSION['message'] = "Compétence mise à jour avec succès !";
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_skill.php?id=" . ($_POST['id'] ?? ''));
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Compétence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-pen-to-square mr-2"></i>Modifier la compétence
                </h2>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 p-4 mb-6 rounded-lg border border-red-200">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($skill['id'] ?? '') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Icône -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icône Font Awesome</label>
                            <input type="text" name="icon" 
                                   value="<?= htmlspecialchars($skill['icon'] ?? '', ENT_QUOTES) ?>" 
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="code (sans le 'fa-')">
                            <p class="text-sm text-gray-500 mt-1">Ex: code pour &lt;i class="fas fa-code"&gt;</p>
                        </div>

                        <!-- Titre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <input type="text" name="title" 
                                   value="<?= htmlspecialchars($skill['title'] ?? '', ENT_QUOTES) ?>" 
                                   required
                                   maxlength="100"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" required
                                  maxlength="255"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($skill['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <a href="manage_skills.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
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