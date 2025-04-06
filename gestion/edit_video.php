<?php
// edit_video.php
session_start();

// ==================== #
# CONFIGURATION DE SÉCURITÉ
// ==================== #

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');

// ==================== #
# VÉRIFICATIONS INITIALES
// ==================== #


// 2. Chargement sécurisé de la configuration
require __DIR__ . '/../connexion/msql.php';

// ==================== #
# FONCTIONS DE SÉCURITÉ
// ==================== #

function validateYoutubeId(string $url): string {
    $patterns = [
        '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $match)) {
            if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $match[1])) {
                return 'https://www.youtube.com/embed/' . $match[1];
            }
        }
    }
    
    throw new InvalidArgumentException("❌ Format d'URL YouTube invalide");
}

function logAction(string $action): void {
    $log = sprintf(
        "[%s][IP:%s][Admin:%s] %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        $_SESSION['admin']['email'] ?? 'unknown',
        $action
    );
    file_put_contents(__DIR__ . '/security.log', $log, FILE_APPEND | LOCK_EX);
}

// ==================== #
# LOGIQUE PRINCIPALE
// ==================== #

try {
    // 3. Gestion CSRF corrigée (parenthèse manquante ajoutée)
    if (empty($_SESSION['csrf_token'])) { // <-- Correction ici
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // <-- Parenthèse en trop supprimée
        $_SESSION['csrf_generated'] = time();
    } elseif (time() - $_SESSION['csrf_generated'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_generated'] = time();
    }

    // 4. Récupération sécurisée de l'ID
    $video_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 999999
        ]
    ]);

    // 5. Chargement des données existantes corrigé
    $video = [];
    if ($video_id) {
        $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new RuntimeException("🕵️ Vidéo introuvable - ID potentiellement falsifié");
        }
        
        $video = $result->fetch_assoc();
        // Suppression de $stmt->close() ici pour éviter la fermeture prématurée
    }

    // 6. Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... (le reste du traitement POST reste inchangé) ...
    }

} catch (Exception $e) {
    // 14. Gestion centralisée des erreurs
    error_log($e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: dashboard.php");
    exit();
} finally {
    // 15. Nettoyage des ressources corrigé
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        try {
            $stmt->close();
        } catch (Exception $e) {
            error_log("Erreur fermeture statement: " . $e->getMessage());
        }
    }
    if ($conn instanceof mysqli) {
        try {
            $conn->close();
        } catch (Exception $e) {
            error_log("Erreur fermeture connexion: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la vidéo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-video mr-2"></i>Modifier la vidéo
                </h1>

                <!-- Affichage des messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg border border-red-200">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg border border-green-200">
                        <?= htmlspecialchars($_SESSION['message']) ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- Protection CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="video_id" value="<?= htmlspecialchars($video['id']) ?>">

                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                        <input type="text" name="title" 
                               value="<?= htmlspecialchars($video['title'] ?? '') ?>" 
                               required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               maxlength="255">
                    </div>

                    <!-- URL YouTube -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL YouTube *
                            <span class="text-gray-400 text-xs">(lien direct ou embed)</span>
                        </label>
                        <input type="url" name="video_url" 
                               value="<?= htmlspecialchars($video['video_url'] ?? '') ?>"
                               required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="https://youtube.com/watch?v=... ou https://youtu.be/...">
                        <p class="text-xs text-gray-500 mt-1">
                            Les formats acceptés : lien direct YouTube ou lien court youtu.be
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  maxlength="500"><?= 
                                  htmlspecialchars($video['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                                Annuler
                            </a>
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

    <!-- Scripts -->
    <script>
        // Conversion automatique des URLs YouTube
        document.querySelector('input[name="video_url"]').addEventListener('blur', function(e) {
            const url = e.target.value;
            const patterns = [
                { 
                    regex: /https:\/\/youtu\.be\/([a-zA-Z0-9_-]{11})/, 
                    replace: 'https://www.youtube.com/embed/$1'
                },
                { 
                    regex: /https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/, 
                    replace: 'https://www.youtube.com/embed/$1'
                }
            ];

            for (const pattern of patterns) {
                if (url.match(pattern.regex)) {
                    e.target.value = url.replace(pattern.regex, pattern.replace);
                    break;
                }
            }
        });
    </script>
</body>
</html>