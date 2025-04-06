<?php
session_start();

// Activer le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de session admin
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit();
}

require __DIR__ . '/../connexion/msql.php';

// Configuration
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';
$allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 Mo

try {
    // Récupération et validation de l'ID
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]) : null;

    if (!$id) {
        $_SESSION['error'] = "ID de projet invalide";
        header("Location: dashboard.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validation CSRF
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception("Validation de formulaire invalide");
        }

        // Récupération des données
        $name = htmlspecialchars($_POST['name']);
        $description = htmlspecialchars($_POST['description']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = in_array($_POST['status'], ['planned', 'in_progress', 'completed']) ? $_POST['status'] : 'planned';
        $alt_text = htmlspecialchars($_POST['alt_text']);
        $project_link = htmlspecialchars($_POST['project_link']);
        $existing_image = $_POST['existing_image'];

        // Validation des dates
        if ($end_date && strtotime($end_date) < strtotime($start_date)) {
            throw new Exception("La date de fin doit être postérieure à la date de début");
        }

        // Gestion de l'image
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $relativePath = $existing_image;
        $oldImage = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Validation du fichier
            if ($_FILES['image']['size'] > $maxFileSize) {
                throw new Exception("Fichier trop volumineux (max 5 Mo)");
            }

            $mimeType = $fileInfo->file($_FILES['image']['tmp_name']);
            if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                throw new Exception("Type de fichier non autorisé");
            }

            // Génération nom sécurisé
            $newFileName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
            $fileDestination = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $fileDestination)) {
                throw new Exception("Échec de l'enregistrement");
            }

            // Récupération ancienne image
            $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $oldImage = $stmt->get_result()->fetch_assoc()['image_path'] ?? '';
            $stmt->close();

            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;
        }

        // Mise à jour en base
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE projects SET 
                name = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                status = ?,
                image_path = ?,
                alt_text = ?,
                project_link = ?,
                updated_at = NOW() 
                WHERE id = ?");

            $stmt->bind_param("ssssssssi", 
                $name,
                $description,
                $start_date,
                $end_date,
                $status,
                $relativePath,
                $alt_text,
                $project_link,
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception("Erreur SQL : " . $stmt->error);
            }

            // Suppression ancienne image
            if ($oldImage && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImage)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
            }

            $conn->commit();
            $_SESSION['success'] = "Projet mis à jour avec succès!";
            header("Location: edit_project.php?id=$id");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            if (isset($fileDestination) && file_exists($fileDestination)) {
                unlink($fileDestination);
            }
            throw $e;
        }
    }

    // Récupération données existantes
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$project) {
        $_SESSION['error'] = "Projet introuvable";
        header("Location: dashboard.php");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: edit_project.php?id=$id");
    exit();
} finally {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le projet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-project-diagram mr-2"></i>Modifier le projet
                </h2>

                <?php if(isset($_SESSION['success'])): ?>
                <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>

                <form action="edit_project.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($project['name']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="planned" <?= $project['status'] === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= $project['status'] === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($project['description']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($project['start_date']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($project['end_date']) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                        <div class="border-2 border-dashed border-purple-200 rounded-lg p-6 cursor-pointer hover:border-purple-400" onclick="document.getElementById('image').click()">
                            <div class="text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-purple-400 mb-4"></i>
                                <p class="font-medium text-gray-600">Glissez-déposez ou cliquez pour uploader</p>
                                <?php if($project['image_path']): ?>
                                    <img src="<?= $project['image_path'] ?>" alt="Preview" class="mt-4 mx-auto max-h-40 rounded-lg">
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="file" name="image" id="image" class="hidden">
                        <input type="hidden" name="existing_image" value="<?= $project['image_path'] ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lien du projet</label>
                        <input type="url" name="project_link" value="<?= htmlspecialchars($project['project_link']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="https://example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description alternative</label>
                        <input type="text" name="alt_text" value="<?= htmlspecialchars($project['alt_text']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="Description pour l'accessibilité">
                    </div>

                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Retour</a>
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
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