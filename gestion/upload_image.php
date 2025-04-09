<?php
// Démarrage de la session pour gérer l'authentification
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../connexion/msql.php';

// Vérification des droits administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

// Configuration du répertoire d'upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/';

// Création du répertoire s'il n'existe pas
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Crée récursivement avec permissions 755
}

// Traitement du formulaire en POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    // Récupération des données du formulaire
    $title = $_POST['title'] ?? '';
    $alt = $_POST['alt'] ?? '';

    // Types de fichiers autorisés
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Récupération des informations du fichier
    $fileName = $_FILES['image']['name'];
    $fileTmpName = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];
    $fileError = $_FILES['image']['error'];

    // Vérification de la présence d'un fichier
    if (!empty($fileName)) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validation du type de fichier et absence d'erreur
        if (in_array($fileExt, $allowedExtensions) && $fileError === 0) {
            // Génération d'un nom de fichier unique
            $newFileName = uniqid('', true) . "." . $fileExt;
            $fileDestination = $uploadDir . $newFileName;
            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/' . $newFileName;

            // Déplacement du fichier uploadé
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Insertion en base de données
                $stmt = $conn->prepare("INSERT INTO images (filename, filepath, title) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $newFileName, $relativePath, $title);

                if ($stmt->execute()) {
                    echo "Image ajoutée avec succès.";
                } else {
                    echo "Erreur lors de l'ajout de l'image dans la base de données.";
                }
                $stmt->close();
            } else {
                echo "Erreur lors de l'upload du fichier.";
            }
        } else {
            echo "Type de fichier non autorisé ou erreur lors de l'upload.";
        }
    } else {
        echo "Aucun fichier sélectionné.";
    }
}

// Récupération de la dernière image ajoutée
$lastImageQuery = "SELECT * FROM images ORDER BY id DESC LIMIT 1";
$lastImageResult = $conn->query($lastImageQuery);
$lastImage = $lastImageResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter des images</title>
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
                    <i class="fas fa-image mr-2"></i>Ajouter une image
                </h1>

                <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                    <!-- Champ Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Titre descriptif">
                    </div>

                    <!-- Zone de dépôt drag & drop -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image *</label>
                        <div id="drop-zone" 
                             class="border-2 border-dashed border-purple-200 rounded-lg p-6 text-center cursor-pointer hover:border-purple-400 transition-colors">
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>Glissez et déposez votre image ici
                            </p>
                            <p class="text-sm text-gray-400">ou cliquez pour sélectionner</p>
                            <input type="file" name="image" id="image" class="hidden" 
                                   accept=".jpg, .jpeg, .png, .webp">
                            <img id="preview" src="" alt="Aperçu de l'image" 
                                 class="mt-4 mx-auto hidden max-w-full h-48 object-cover rounded-lg shadow-md">
                        </div>
                    </div>

                    <!-- Champ Description alternative -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description alternative *</label>
                        <input type="text" name="alt" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Décrivez l'image pour l'accessibilité">
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Publier l'image
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Affichage de la dernière image ajoutée -->
        <?php if ($lastImage): ?>
        <div class="mt-8 bg-white shadow-xl rounded-lg border border-purple-100 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-purple-800 mb-4">
                    <i class="fas fa-history mr-2"></i>Dernière image ajoutée
                </h2>
                <img class="w-full h-64 object-cover rounded-lg mb-4"
                    src="/BUT2/S4/Portofolio-Back/lib/uploadPhoto/<?= htmlspecialchars($lastImage['filename']) ?>"
                    alt="<?= htmlspecialchars($lastImage['title']) ?>">
                <div class="space-y-2">
                    <p class="text-gray-900 font-medium"><?= htmlspecialchars($lastImage['title']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($lastImage['filename']) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
