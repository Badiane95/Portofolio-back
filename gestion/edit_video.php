<?php
// edit_video.php
session_start();
require __DIR__ . '/../connexion/msql.php';

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les données existantes
$video = [];
if (isset($_GET['id'])) {
    $video_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Vidéo introuvable";
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $video_id = intval($_POST['video_id']);
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $video_url = filter_var($_POST['video_url'], FILTER_VALIDATE_URL);

        // Validation avancée de l'URL
        if (!preg_match('/^https:\/\/www\.youtube\.com\/embed\/[a-zA-Z0-9_-]{11}(\?.*)?$/', $video_url)) {
            throw new Exception("Format d'URL YouTube invalide");
        }

        $stmt = $conn->prepare("UPDATE videos SET title = ?, description = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $video_url, $video_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Vidéo mise à jour avec succès";
            // Audit log
            $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, target) VALUES (?, ?, ?)");
            $log_action = "UPDATE_VIDEO";
            $log_stmt->bind_param("iss", $_SESSION['user_id'], $log_action, $video_id);
            $log_stmt->execute();
            header("Location: dashboard.php");
            exit();
        } else {
            throw new Exception("Erreur de mise à jour : " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_video.php?id=" . $video_id);
        exit();
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
</head>
<body class="bg-purple-50">
    
<div class="container mx-auto p-6 max-w-4xl">
    <a href="dashboard.php" class="inline-block mb-6 text-purple-600 hover:text-purple-800">
        ← Retour au dashboard
    </a>
    
    <div class="bg-white rounded-xl shadow-lg p-8 border-2 border-purple-100">
        <h1 class="text-2xl font-bold text-purple-700 mb-6">Modifier la vidéo</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
        <input type="hidden" name="id" value="<?= $video['id'] ?>">
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-purple-700">Titre</label>
                <input type="text" name="title" value="<?= htmlspecialchars($video['title']) ?>" 
                    class="w-full px-4 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-purple-700">URL YouTube (embed)</label>
                <input type="url" name="video_url" value="<?= htmlspecialchars($video['video_url']) ?>"
                    pattern="https://www\.youtube\.com/embed/.+"
                    class="w-full px-4 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-purple-700">Description</label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"><?= 
                    htmlspecialchars($video['description']) ?></textarea>
            </div>
            
            <div class="flex justify-end gap-4">
                <button type="reset" class="px-6 py-2 text-purple-700 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100">
                    Réinitialiser
                </button>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
