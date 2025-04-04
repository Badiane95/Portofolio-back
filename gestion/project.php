<?php
session_start();

include __DIR__ . '/../connexion/msql.php';

// Configuration sécurisée du répertoire d'upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 Mo

try {
    if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception("Erreur lors de la création du répertoire");
    }
} catch (Exception $e) {
    die("Erreur système : " . $e->getMessage());
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validation CSRF (à implémenter selon votre système)
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Validation de formulaire invalide");
        }

        // Nettoyage des entrées
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $status = in_array($_POST['status'] ?? '', ['planned', 'in_progress', 'completed']) ? $_POST['status'] : 'planned';
        $alt = filter_input(INPUT_POST, 'alt', FILTER_SANITIZE_SPECIAL_CHARS);
        $project_link = filter_input(INPUT_POST, 'project_link', FILTER_VALIDATE_URL);

        // Validation des dates
        if (!empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
            throw new Exception("La date de fin doit être postérieure à la date de début");
        }

        // Gestion du fichier
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Veuillez sélectionner une image valide");
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($_FILES['image']['tmp_name']);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception("Type de fichier non autorisé");
        }

        if ($_FILES['image']['size'] > $maxFileSize) {
            throw new Exception("La taille du fichier dépasse 5 Mo");
        }

        // Génération du nom de fichier sécurisé
        $fileExtension = array_search($mimeType, [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ]);

        $newFileName = bin2hex(random_bytes(16)) . "." . $fileExtension;
        $fileDestination = $uploadDir . $newFileName;
        $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $fileDestination)) {
            throw new Exception("Erreur lors de l'enregistrement du fichier");
        }

        // Préparation de la requête SQL
        $sql = "INSERT INTO projects 
                (name, description, start_date, end_date, status, image_path, alt_text, project_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête");
        }

        $stmt->bind_param("ssssssss", 
            $name,
            $description,
            $start_date,
            $end_date,
            $status,
            $relativePath,
            $alt,
            $project_link
        );

        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution de la requête : " . $stmt->error);
        }

        $message = "Projet ajouté avec succès !";
        $stmt->close();

        // Réinitialisation du formulaire après succès
        $_POST = array();

    } catch (Exception $e) {
        $error = $e->getMessage();
        // Nettoyage du fichier en cas d'erreur
        if (isset($fileDestination) && file_exists($fileDestination)) {
            unlink($fileDestination);
        }
    }
}

// Génération du token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un projet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="dropZone.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-project-diagram mr-2"></i>Ajouter un projet
                </h1>

                <?php if ($message): ?>
                    <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom du projet -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du projet *</label>
                            <input type="text" name="name" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Nom du projet"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <select name="status" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="planned" <?= ($_POST['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= ($_POST['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= ($_POST['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="4" required
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="Décrivez le projet..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dates -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début *</label>
                            <input type="date" name="start_date" required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <input type="date" name="end_date"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image du projet *</label>
                        <div id="drop-zone" class="border-2 border-dashed border-purple-200 rounded-lg p-6 text-center cursor-pointer hover:border-purple-400 transition-colors">
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>Glissez et déposez votre image ici
                            </p>
                            <p class="text-sm text-gray-400">Formats acceptés : JPG, PNG, WEBP (max 5 Mo)</p>
                            <input class="hidden" type="file" id="image" name="image" accept="image/*" required>
                            <button type="button" id="select-file" 
                                    class="mt-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                Sélectionner un fichier
                            </button>
                        </div>
                        <img id="preview" src="" alt="Aperçu de l'image" 
                             class="mt-4 mx-auto hidden max-w-full h-48 object-cover rounded-lg shadow-md">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Texte alternatif *</label>
                        <input type="text" name="alt" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Description de l'image"
                               value="<?= htmlspecialchars($_POST['alt'] ?? '') ?>">
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>Ajouter le projet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>