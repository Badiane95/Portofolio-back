<?php
// edit_video.php
session_start();

// ==================== #
# CONFIGURATION DE SÉCURITÉ
// ==================== #

header('X-Content-Type-Options: nosniff'); // Protège contre le MIME-sniffing
header('X-Frame-Options: DENY'); // Empêche le clickjacking
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload'); // Force HTTPS, attention à bien configurer le serveur

// ==================== #
# VÉRIFICATIONS INITIALES
// ==================== #

// 1. Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "🔒 Accès réservé aux administrateurs";
    header("Location: ../login/session.php");
    exit;
}

// 2. Chargement sécurisé de la configuration
require __DIR__ . '/../connexion/msql.php';

// ==================== #
# FONCTIONS DE SÉCURITÉ
// ==================== #

/**
 * Valide et convertit une URL YouTube en URL d'intégration sécurisée.
 * @param string $url L'URL YouTube à valider.
 * @return string L'URL d'intégration sécurisée.
 * @throws InvalidArgumentException Si l'URL n'est pas valide.
 */
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

/**
 * Enregistre une action dans un fichier de log de sécurité.
 * @param string $action L'action à enregistrer.
 * @return void
 */
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
    // 3. Gestion CSRF
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

    // 5. Chargement des données existantes
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
    }

    // 6. Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 7. Validation CSRF stricte
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            logAction("Tentative de CSRF détectée");
            throw new RuntimeException("⚠️ Token de sécurité invalide - Veuillez rafraîchir la page");
        }

        // 8. Validation des données
        $post_video_id = filter_input(INPUT_POST, 'video_id', FILTER_VALIDATE_INT);
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $raw_url = filter_input(INPUT_POST, 'video_url', FILTER_SANITIZE_URL);

        // 9. Cohérence des IDs
        if ($video_id !== $post_video_id) {
            logAction("Tentative de modification d'ID vidéo: $post_video_id");
            throw new RuntimeException("❌ Altération d'identifiant détectée");
        }

        // 10. Validation des longueurs

        if (mb_strlen($title) === 0 || mb_strlen($title) > 500) {
            throw new InvalidArgumentException("📏 Le titre doit contenir entre 1 et 500 caractères");
        }
        if (mb_strlen($description) > 2000) {
            throw new InvalidArgumentException("📏 La description ne peut excéder 2000 caractères");
        }

        // 11. Conversion URL YouTube
        $video_url = validateYoutubeId($raw_url);

        // 12. Mise à jour sécurisée
        $stmt = $conn->prepare("UPDATE videos SET
            title = ?,
            description = ?,
            video_url = ?,
            updated_at = NOW()
            WHERE id = ?");

        $stmt->bind_param("sssi", $title, $description, $video_url, $video_id);

        if (!$stmt->execute()) {
            throw new mysqli_sql_exception("💾 Erreur base de données : " . $stmt->error);
        }

        // 13. Journalisation
        logAction("Modification vidéo #$video_id : " . json_encode([
            'old_title' => $video['title'],
            'new_title' => $title
        ]));

        $_SESSION['message'] = "✅ Vidéo mise à jour avec succès";
        header("Location: dashboard.php");
        exit();
    }

} catch (Exception $e) {
    // 14. Gestion centralisée des erreurs
    error_log($e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: " . ($video_id ? "edit_video.php?id=$video_id" : "dashboard.php"));
    exit();
} finally {
    // 15. Nettoyage des ressources
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
                    <div class="relative">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                        <input type="text" id="title" name="title" 
                            value="<?= htmlspecialchars_decode($video['title'] ?? '', ENT_QUOTES) ?>" 
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 pr-16"
                            maxlength="500">
                        <div class="absolute right-3 bottom-2 text-xs text-gray-400">
                            <span id="title_counter"><?= 500 - mb_strlen(htmlspecialchars_decode($video['title'] ?? '')) ?></span>/500
                        </div>
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
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            maxlength="2000"><?= htmlspecialchars_decode($video['description'] ?? '', ENT_QUOTES) ?></textarea>
                         <div class="absolute right-3 bottom-2 text-xs text-gray-400">
                            <span id="description_counter"><?= 2000 - mb_strlen(htmlspecialchars_decode($video['description'] ?? '')) ?></span>/2000
                        </div>
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
    <script>
        // Compteur de caractères en temps réel pour le titre
        document.querySelector('input[name="title"]').addEventListener('input', function(e) {
            const counter = document.getElementById('title_counter');
            const remaining = 500 - e.target.value.length;
            counter.textContent = remaining;
            counter.style.color = remaining < 50 ? '#dc2626' : '#6b7280';
        });
    </script>
     <script>
        // Compteur de caractères en temps réel pour la description
        document.querySelector('textarea[name="description"]').addEventListener('input', function(e) {
            const counter = document.getElementById('description_counter');
            const remaining = 2000 - e.target.value.length;
            counter.textContent = remaining;
            counter.style.color = remaining < 100 ? '#dc2626' : '#6b7280';
        });
    </script>
</body>
</html>
