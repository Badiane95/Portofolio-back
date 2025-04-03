<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

$message = '';
$error = '';
$project = [];

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupération et validation des données
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $status = in_array($_POST['status'], ['planned', 'in_progress', 'completed']) ? $_POST['status'] : 'planned';
        $alt_text = filter_input(INPUT_POST, 'alt_text', FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Gestion de l'image
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';
        $relativePath = $_POST['existing_image'] ?? '';
        $newFileName = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($_FILES['image']['tmp_name']);
            
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('project_', true) . '.' . $fileExt;
                $fileDestination = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $fileDestination)) {
                    $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;
                }
            }
        }

        // Mise à jour en base
        $stmt = $conn->prepare("UPDATE projects SET 
            name = ?,
            description = ?,
            start_date = ?,
            end_date = ?,
            status = ?,
            image_path = ?,
            alt_text = ?,
            updated_at = CURRENT_TIMESTAMP 
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

        if ($stmt->execute()) {
            $message = "Projet mis à jour avec succès!";
        } else {
            throw new Exception("Erreur de mise à jour : " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        // Récupération des données existantes
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        
        if (!$project) {
            throw new Exception("Projet introuvable");
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le projet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="dropZone.js" defer></script>
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Modifier le projet</h2>
                
                <?php if($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form action="edit_project.php" method="POST" enctype="multipart/form-data">
                    <div class="grid gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du projet</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($project['name'] ?? '') ?>" 
                                   class="w-full p-2 border rounded-md" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="4" 
                                      class="w-full p-2 border rounded-md"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                <input type="date" name="start_date" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>" 
                                       class="w-full p-2 border rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                                <input type="date" name="end_date" value="<?= htmlspecialchars($project['end_date'] ?? '') ?>" 
                                       class="w-full p-2 border rounded-md">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                            <select name="status" class="w-full p-2 border rounded-md">
                                <option value="planned" <?= ($project['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= ($project['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>

                        <!-- ... (le reste du code PHP/HTML avant la section image) ... -->

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Image du projet</label>
    
    <!-- Dropzone Container -->
    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 mb-4 cursor-pointer hover:border-blue-500 transition-colors">
        <div class="text-center">
            <!-- Icône et texte d'instruction -->
            <div class="mb-4">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                <p class="font-medium text-gray-600">Glissez et déposez votre image ici</p>
                <p class="text-sm text-gray-500">ou cliquez pour sélectionner</p>
            </div>

            <!-- Aperçu de l'image -->
            <?php if($project['image_path'] ?? ''): ?>
                <img id="preview" src="<?= htmlspecialchars($project['image_path']) ?>" 
                     alt="<?= htmlspecialchars($project['alt_text'] ?? '') ?>" 
                     class="mb-4 max-h-40 mx-auto object-cover rounded-md">
            <?php else: ?>
                <img id="preview" src="#" alt="Aperçu de l'image" class="mb-4 max-h-40 mx-auto object-cover rounded-md hidden">
            <?php endif; ?>
        </div>
    </div>

    <!-- Input file caché -->
    <input type="file" name="image" id="image" class="hidden">
    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($project['image_path'] ?? '') ?>">

    <!-- Champ texte alternatif -->
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Texte alternatif (Alt)</label>
        <input type="text" name="alt_text" value="<?= htmlspecialchars($project['alt_text'] ?? '') ?>" 
               class="w-full p-2 border rounded-md" placeholder="Description de l'image pour l'accessibilité">
    </div>
</div>

<!-- ... (le reste du formulaire) ... -->

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Texte alternatif (alt)</label>
                            <input type="text" name="alt_text" value="<?= htmlspecialchars($project['alt_text'] ?? '') ?>" 
                                   class="w-full p-2 border rounded-md">
                        </div>
                    </div>

                    <input type="hidden" name="id" value="<?= htmlspecialchars($project['id'] ?? '') ?>">
                    
                    <div class="flex justify-between items-center mt-8">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-2"></i>Retour au dashboard
                        </a>
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