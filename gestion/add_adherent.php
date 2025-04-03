<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

include __DIR__ . '/../connexion/msql.php';

// Configuration du dossier d'upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadProfil/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $newPhoto = null;

    // Gestion de l'upload de photo
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];

        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Vérification du type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($fileType, $allowedTypes)) {
            die("Erreur : Type de fichier non autorisé (JPEG, PNG, GIF, WEBP uniquement)");
        }

        if ($file['size'] > $maxSize) {
            die("Erreur : Le fichier est trop volumineux (max 2MB)");
        }

        // Génération d'un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $newPhoto = '/BUT2/S4/Portofolio-Back/lib/uploadProfil/' . $filename;
        } else {
            die("Erreur lors du téléversement du fichier.");
        }
    }

    // Préparation de la requête SQL
    $query = $conn->prepare("INSERT INTO adherents (nom, prenom, email, profile_photo) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $nom, $prenom, $email, $newPhoto);

    if ($query->execute()) {
        header("Location: ../gestion/dashboard.php");
    } else {
        echo "Erreur : " . $query->error;
    }

    $query->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un adhérent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
<?php include 'navback.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
            <div class="md:flex">
                <div class="p-8 w-full">
                <h2 class="text-2xl font-bold text-purple-600 mb-6">Ajouter un adhérent</h2>
                    <form action="add_adherent.php" method="POST" enctype="multipart/form-data">
                        <div class="space-y-4">
                            <!-- Photo de profil -->
                            <div class="text-center">
                                <label class="cursor-pointer inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Ajouter une photo
                                    <input type="file"
                                           name="profile_photo"
                                           class="hidden"
                                           accept="image/*">
                                </label>
                            </div>

                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" id="nom" name="nom"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input type="text" id="prenom" name="prenom"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="../gestion/dashboard.php" class="inline-block bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Retour au Dashboard
            </a>
        </div>
    </div>
</body>
</html>

