<?php
// edit_video.php
session_start();
require __DIR__ . '/../connexion/msql.php';

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

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                        <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $video['id'] ?>">

                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                        <input type="text" name="title" 
                               value="<?= htmlspecialchars($video['title']) ?>" 
                               required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <!-- URL YouTube -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL Embed YouTube *</label>
                        <input type="url" name="video_url" 
                               value="<?= htmlspecialchars($video['video_url']) ?>"
                               pattern="https://www\.youtube\.com/embed/.+"
                               required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="https://www.youtube.com/embed/ID_VIDEO">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= 
                                  htmlspecialchars($video['description']) ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <button type="reset" class="text-gray-600 hover:text-gray-800">
                                Réinitialiser
                            </button>
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