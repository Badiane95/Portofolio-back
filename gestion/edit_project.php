<?php
session_start(); // Démarre ou reprend une session existante

// Activer le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit(); // Termine l'exécution du script
}

// Connexion BDD
require __DIR__ . '/../connexion/msql.php';

// Vérifier connexion
if ($conn->connect_error) {
    error_log("Erreur DB: " . $conn->connect_error); // Enregistre l'erreur dans le journal du serveur
    die("Erreur système - veuillez réessayer plus tard"); // Affiche un message d'erreur à l'utilisateur
}

try {
    // Validation de l'ID du projet
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) throw new Exception("ID projet invalide"); // Lance une exception si l'ID n'est pas valide

    // Traitement POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Validation CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception("Validation de sécurité échouée"); // Lance une exception si la validation CSRF échoue
        }

        // Récupération données du formulaire
        $name = htmlspecialchars($_POST['name']); // Nom du projet
        $description = htmlspecialchars($_POST['description']); // Description du projet
        $start_date = $_POST['start_date']; // Date de début du projet
        $end_date = $_POST['end_date']; // Date de fin du projet
        $status = in_array($_POST['status'], ['planned', 'in_progress', 'completed']) ? $_POST['status'] : 'planned'; // Statut du projet
        $alt_text = htmlspecialchars($_POST['alt_text']); // Texte alternatif de l'image du projet
        $project_link = htmlspecialchars($_POST['project_link']); // Lien vers le projet
        $existing_image = $_POST['existing_image']; // Chemin de l'image existante

        // Configuration upload
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/'; // Dossier d'upload
        $maxFileSize = 5 * 1024 * 1024; // 5 Mo // Taille maximale du fichier
        $allowedTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp']; // Types de fichiers autorisés

        // Vérification dossier upload
        if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception("Impossible de créer le dossier d'upload"); // Lance une exception si la création du dossier d'upload échoue
        }

        // Gestion image
        $relativePath = $existing_image; // Chemin relatif de l'image
        $oldImage = ''; // Chemin de l'ancienne image

        // Vérification si une nouvelle image a été uploadée
        if (!empty($_FILES['image']['tmp_name'])) {
            // Validation fichier
            $finfo = new finfo(FILEINFO_MIME_TYPE); // Ouvre un flux pour la vérification du type MIME
            $mimeType = $finfo->file($_FILES['image']['tmp_name']); // Récupère le type MIME du fichier
            $extension = array_search($mimeType, $allowedTypes, true); // Récupère l'extension du fichier

            // Vérifie si la taille du fichier est autorisée
            if ($_FILES['image']['size'] > $maxFileSize) {
                throw new Exception("Fichier trop volumineux (>5Mo)"); // Lance une exception si le fichier est trop volumineux
            }

            // Vérifie si le type de fichier est autorisé
            if (!$extension) {
                throw new Exception("Type de fichier non autorisé"); // Lance une exception si le type de fichier n'est pas autorisé
            }

            // Génération nom unique pour le fichier
            $newFileName = 'project-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
            $filePath = $uploadDir . $newFileName;

            // Déplace le fichier uploadé
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                throw new Exception("Échec de l'upload"); // Lance une exception si le déplacement du fichier échoue
            }

            // Récupération ancienne image
            $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $oldImage = $stmt->get_result()->fetch_assoc()['image_path'];
            $stmt->close();

            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName; // Nouveau chemin relatif de l'image
        }

        // Mise à jour BDD
        $stmt = $conn->prepare("UPDATE projects SET 
            name=?, description=?, start_date=?, end_date=?, status=?, 
            image_path=?, alt_text=?, project_link=?, updated_at=NOW() 
            WHERE id=?");

        $stmt->bind_param("ssssssssi", 
            $name, $description, $start_date, $end_date, $status,
            $relativePath, $alt_text, $project_link, $id
        );

        // Exécute la requête
        if (!$stmt->execute()) {
            throw new Exception("Erreur SQL: " . $stmt->error); // Lance une exception en cas d'erreur SQL
        }

        // Suppression ancienne image
        if (!empty($oldImage) && $oldImage !== $relativePath) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $oldImage; // Chemin complet de l'ancienne image
            // Vérifie si le fichier existe et le supprime
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $_SESSION['success'] = "Projet mis à jour !"; // Enregistre un message de succès dans la session
        header("Location: edit_project.php?id=$id"); // Redirige vers la page d'édition du projet
        exit(); // Termine l'exécution du script
    }

    // Récupération données existantes
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc(); // Récupère les informations du projet
    $stmt->close();

    // Si le projet n'est pas trouvé, lance une exception
    if (!$project) throw new Exception("Projet introuvable");

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage(); // Enregistre le message d'erreur dans la session
    header("Location: edit_project.php?id=" . ($id ?? '')); // Redirige vers la page d'édition du projet
    exit(); // Termine l'exécution du script
} finally {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un nouveau token CSRF
    // Ferme la connexion à la base de données
    if (isset($conn)) $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le projet</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration du script pour la zone de dépôt (dropZone.js) -->
    <script src="dropZone.js" defer></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-project-diagram mr-2"></i>Modifier le projet
                </h2>

                <!-- Affichage des messages -->
                <?php if(isset($_SESSION['success'])): ?>
                <!-- Message de succès -->
                <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); // Suppression du message de la session ?>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                <!-- Message d'erreur -->
                <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); // Suppression du message de la session ?>
                </div>
                <?php endif; ?>

                <!-- Formulaire de modification du projet -->
                <form action="edit_project.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Token CSRF pour la sécurité -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <!-- ID du projet -->
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <!-- Champs du formulaire -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom du projet -->
                        <div>
                            <!-- Label pour le nom -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <!-- Input pour le nom -->
                            <input type="text" name="name" value="<?= htmlspecialchars_decode($project['name'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" required>
                        </div>

                        <!-- Statut du projet -->
                        <div>
                            <!-- Label pour le statut -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <!-- Select pour le statut -->
                            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="planned" <?= ($project['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= ($project['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description du projet -->
                    <div>
                        <!-- Label pour la description -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <!-- Textarea pour la description -->
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars_decode($project['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <!-- Dates du projet -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date de début -->
                        <div>
                            <!-- Label pour la date de début -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <!-- Input pour la date de début -->
                            <input type="date" name="start_date" value="<?= htmlspecialchars_decode($project['start_date'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <!-- Date de fin -->
                        <div>
                            <!-- Label pour la date de fin -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <!-- Input pour la date de fin -->
                            <input type="date" name="end_date" value="<?= htmlspecialchars_decode($project['end_date'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <!-- Zone de dépôt d'image -->
                    <div>
                        <!-- Label pour l'image -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                        <!-- Zone de dépôt -->
                        <div id="drop-zone" class="border-2 border-dashed border-purple-200 rounded-lg p-6 cursor-pointer hover:border-purple-400">
                            <div class="text-center">
                                <!-- Affichage de l'image existante ou indication de dépôt -->
                                <?php if(!empty($project['image_path'])): ?>
                                    <!-- Image existante -->
                                    <img id="preview" src="<?= htmlspecialchars($project['image_path']) ?>" 
                                         alt="Preview" class="mt-4 mx-auto max-h-40 rounded-lg">
                                <?php else: ?>
                                    <!-- Indication de dépôt -->
                                    <i class="fas fa-cloud-upload-alt text-4xl text-purple-400 mb-4"></i>
                                    <p class="font-medium text-gray-600">Glissez-déposez ou cliquez pour uploader</p>
                                    <img id="preview" src="" alt="Preview" class="mt-4 mx-auto max-h-40 rounded-lg hidden">
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Input de type fichier caché -->
                        <input type="file" name="image" id="image" class="hidden">
                        <!-- Input caché pour le chemin de l'image existante -->
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($project['image_path'] ?? '') ?>">
                    </div>

                    <!-- Autres champs -->
                    <div>
                        <!-- Label pour le lien du projet -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lien du projet</label>
                        <!-- Input pour le lien du projet -->
                        <input type="url" name="project_link" 
                               value="<?= htmlspecialchars_decode($project['project_link'] ?? '', ENT_QUOTES) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="https://example.com">
                    </div>

                    <div>
                        <!-- Label pour la description alternative -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description alternative</label>
                        <!-- Input pour la description alternative -->
                        <input type="text" name="alt_text" 
                               value="<?= htmlspecialchars_decode($project['alt_text'] ?? '', ENT_QUOTES) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="Description pour l'accessibilité">
                    </div>

                    <!-- Section pour les boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <!-- Lien pour retourner au tableau de bord -->
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Retour</a>
                            <!-- Bouton pour enregistrer les modifications -->
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script pour la zone de dépôt -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('preview');
        const existingImageInput = document.querySelector('input[name="existing_image"]');

        // Gestion du drag & drop
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('border-purple-400', 'bg-purple-50');
        });

        dropZone.addEventListener('dragleave', e => {
            e.preventDefault();
            dropZone.classList.remove('border-purple-400', 'bg-purple-50');
        });

        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-purple-400', 'bg-purple-50');
            
            const file = e.dataTransfer.files[0];
            handleFile(file);
            fileInput.files = e.dataTransfer.files;
        });

        // Gestion du clic
        dropZone.addEventListener('click', () => fileInput.click());

        // Gestion du changement de fichier
        fileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            handleFile(file);
        });

        function handleFile(file) {
            if (file && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else if (file) {
                alert('Veuillez sélectionner une image valide (JPEG, PNG ou WebP).');
                fileInput.value = '';
                preview.src = existingImageInput.value;
                preview.classList.toggle('hidden', !existingImageInput.value);
            }
        }
    });
    </script>
</body>
</html>
