<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $title = $_POST['title'] ?? '';
    $alt = $_POST['alt'] ?? '';

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Récupère les informations sur le fichier uploadé
    $fileName = $_FILES['image']['name'];
    $fileTmpName = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];
    $fileError = $_FILES['image']['error'];

    // Vérifie si un fichier a été uploadé
    if (!empty($fileName)) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExtensions) && $fileError === 0) {
            // Génère un nom de fichier unique
            $newFileName = uniqid('', true) . "." . $fileExt;
            $fileDestination = $uploadDir . $newFileName;
            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/' . $newFileName;

            // Déplace le fichier uploadé vers sa destination finale
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Prépare la requête SQL pour insérer les informations de l'image dans la base de données
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
</head>

<body class="bg-zinc-100 font-sans">
    <header>
        <?php include 'navback.php'; ?>
    </header>

    <main class="container mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <h1 class="text-3xl font-bold text-purple-700 mb-6">Ajouter une image</h1>
                <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                        <input type="text" id="title" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Titre de l'image">
                    </div>

                    <div id="drop-zone"
                        class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-purple-500 transition-colors duration-300">
                        <p class="text-gray-600 mb-2">Glissez et déposez votre image ici</p>
                        <p class="text-sm text-gray-400">ou cliquez pour sélectionner</p>
                        <!-- Input file modifié pour accepter uniquement les formats jpg, jpeg, png et webp -->
                        <input type="file" name="image" id="image" class="hidden" accept=".jpg, .jpeg, .png, .webp">
                        <img id="preview" src="" alt="Aperçu de l'image"
                            class="mt-4 mx-auto hidden max-w-full h-auto rounded-lg shadow-md">
                    </div>

                    <div>
                        <label for="alt" class="block text-sm font-medium text-gray-700 mb-1">Texte alternatif (Alt)</label>
                        <input type="text" id="alt" name="alt"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Description de l'image pour l'accessibilité">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-6 py-2 bg-purple-600 text-white font-medium rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($lastImage): ?>
        <section class="mt-8 bg-white shadow-lg rounded-xl overflow-hidden">
            <h2 class="sr-only">Dernière image uploadée</h2>
            <img class="w-full h-64 object-cover"
                src="/BUT2/S4/Portofolio-Back/lib/uploadPhoto/<?php echo htmlspecialchars($lastImage['filename']); ?>"
                alt="<?php echo htmlspecialchars($lastImage['title']); ?>">
            <div class="p-6">
                <p class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($lastImage['title']); ?></p>
                <p class="text-sm text-gray-500">Nom du fichier : <?= htmlspecialchars($lastImage['filename']); ?></p>
            </div>
        </section>
        <?php endif; ?>
    </main>
</body>

</html>
