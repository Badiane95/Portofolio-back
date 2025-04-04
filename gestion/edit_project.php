<?php
session_start();

// Vérification de session admin sécurisée
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header("HTTP/1.1 403 Forbidden");
    exit("Accès refusé");
}

require __DIR__ . '/../connexion/msql.php';

// Configuration
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';
$allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 Mo

$message = '';
$error = '';
$project = [];

try {
    // Récupération de l'ID avec validation
    $id = filter_input(INPUT_REQUEST, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]) ?? null;

    if (!$id) {
        throw new Exception("ID de projet invalide");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validation CSRF
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception("Validation de formulaire invalide");
        }

        // Récupération et validation des données
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $status = in_array($_POST['status'] ?? '', ['planned', 'in_progress', 'completed']) 
                ? $_POST['status'] 
                : 'planned';
        $alt_text = filter_input(INPUT_POST, 'alt_text', FILTER_SANITIZE_SPECIAL_CHARS);
        $existing_image = $_POST['existing_image'] ?? '';

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
                throw new Exception("Le fichier dépasse la taille maximale autorisée (5 Mo)");
            }

            $mimeType = $fileInfo->file($_FILES['image']['tmp_name']);
            
            if (!array_key_exists($mimeType, $allowedMimeTypes)) {
                throw new Exception("Type de fichier non autorisé");
            }

            // Génération nom de fichier sécurisé
            $newFileName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
            $fileDestination = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $fileDestination)) {
                throw new Exception("Échec de l'enregistrement du fichier");
            }

            // Conservation ancienne image pour suppression
            $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $oldImage = $stmt->get_result()->fetch_assoc()['image_path'] ?? '';
            $stmt->close();

            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;
        }

        // Mise à jour en base avec transaction
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
                updated_at = NOW() 
                WHERE id = ?");

            $stmt->bind_param("sssssssi", 
                $name,
                $description,
                $start_date,
                $end_date,
                $status,
                $relativePath,
                $alt_text,
                $id
            );

            if (!$stmt->execute()) {
                throw new Exception("Erreur de mise à jour : " . $stmt->error);
            }

            // Suppression ancienne image si changement
            if ($oldImage && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImage)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
            }

            $conn->commit();
            $message = "Projet mis à jour avec succès!";
            $stmt->close();

        } catch (Exception $e) {
            $conn->rollback();
            if (isset($fileDestination) unlink($fileDestination);
            throw $e;
        }

    } else {
        // Récupération des données existantes
        $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        
        if (!$project) {
            throw new Exception("Projet introuvable");
        }
        $stmt->close();
    }

} catch (Exception $e) {
    $error = $e->getMessage();
} finally {
    // Génération token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?> 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le projet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="dropZone.js" defer></script>
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-project-diagram mr-2"></i>Modifier le projet
                </h2>

                <?php if($message): ?>
                <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form action="edit_project.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($project['id'] ?? '') ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom du projet -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <input type="text" name="name" 
                                   value="<?= htmlspecialchars($project['name'] ?? '') ?>" 
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <select name="status" 
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="planned" <?= ($project['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= ($project['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dates -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" 
                                   value="<?= htmlspecialchars($project['start_date'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <input type="date" name="end_date" 
                                   value="<?= htmlspecialchars($project['end_date'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image du projet</label>
                        <div id="drop-zone" class="border-2 border-dashed border-purple-200 rounded-lg p-6 cursor-pointer hover:border-purple-400 transition-colors">
                            <div class="text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-purple-400 mb-4"></i>
                                <p class="font-medium text-gray-600">Glissez et déposez votre image ici</p>
                                <p class="text-sm text-gray-400 mt-2">ou cliquez pour sélectionner</p>
                                <?php if($project['image_path'] ?? ''): ?>
                                    <img id="preview" src="<?= htmlspecialchars($project['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($project['alt_text'] ?? '') ?>" 
                                         class="mt-4 mx-auto max-h-40 object-cover rounded-lg shadow-md">
                                <?php else: ?>
                                    <img id="preview" src="#" alt="Aperçu de l'image" class="mt-4 mx-auto max-h-40 hidden">
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="file" name="image" id="image" class="hidden">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($project['image_path'] ?? '') ?>">
                    </div>
  <!-- Lien du projet -->
  <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lien du projet</label>
                        <input type="url" name="project_link"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="https://example.com/project"
                               value="<?= htmlspecialchars($_POST['project_link'] ?? '') ?>">
                    </div>

                    <!-- Texte alternatif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description alternative</label>
                        <input type="text" name="alt_text" 
                               value="<?= htmlspecialchars($project['alt_text'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Décrivez l'image pour l'accessibilité">
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Retour</a>
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
<?php 
$conn->close();
?>