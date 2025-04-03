<?php
session_start();

// Vérification de l'admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données avec validation moderne
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $status = in_array($_POST['status'] ?? '', ['planned', 'in_progress', 'completed']) ? $_POST['status'] : 'planned';
    $alt = filter_input(INPUT_POST, 'alt', FILTER_SANITIZE_SPECIAL_CHARS);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowedExtensions)) {
            $newFileName = uniqid('', true) . "." . $fileExt;
            $fileDestination = $uploadDir . $newFileName;
            $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $fileDestination)) {
                // Requête SQL mise à jour selon la structure de la table
                $sql = "INSERT INTO projects 
                (name, description, start_date, end_date, status, image_path, alt_text) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", 
                    $name,
                    $description,
                    $start_date,
                    $end_date,
                    $status,
                    $relativePath,
                    $alt
                );

                if ($stmt->execute()) {
                    $message = "Projet ajouté avec succès !";
                } else {
                    $error = "Erreur SQL: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Erreur lors de l'upload du fichier";
            }
        } else {
            $error = "Format d'image non supporté";
        }
    } else {
        $error = "Veuillez sélectionner une image valide";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un projet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="dropZone.js" defer></script>
</head>
<body class="bg-gray-100">
    <header>
        <?php include 'navback.php'; ?>
    </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-2xl font-bold text-purple-600 mb-6">Ajouter un projet</h1>

            <?php if ($message): ?>
                <div class="bg-green-200 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-200 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nom du projet</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="name" type="text" name="name" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                              id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">Date de début</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="start_date" type="date" name="start_date" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">Date de fin prévue</label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                               id="end_date" type="date" name="end_date">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Statut</label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="status" name="status" required>
                        <option value="planned">Planifié</option>
                        <option value="in_progress">En cours</option>
                        <option value="completed">Terminé</option>
                    </select>
                </div>

                <div class="mb-4">
    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Image</label>
    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition-colors duration-300">
        <p class="text-gray-600 mb-2">Glissez et déposez votre image ici</p>
        <p class="text-sm text-gray-400">ou</p>
        <input class="hidden" type="file" id="image" name="image" accept="image/*" required>
        <button type="button" id="select-file" class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Sélectionner un fichier
        </button>
    </div>
    <img id="preview" src="" alt="Aperçu de l'image" class="mt-4 mx-auto hidden max-w-full h-auto rounded-lg shadow-md">
</div>


                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="alt">Texte alternatif (alt)</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="text" id="alt" name="alt" required>
                </div>

                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit">Ajouter le projet</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>