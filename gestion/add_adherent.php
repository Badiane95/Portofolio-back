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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-user-plus mr-2"></i>Ajouter un adhérent
                </h2>

                <form action="add_adherent.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Photo de profil -->
                    <div class="text-center">
                        <label class="cursor-pointer inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>
                            Ajouter une photo
                            <input type="file" 
                                   name="profile_photo"
                                   class="hidden"
                                   accept="image/*">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                                <input type="text" name="nom" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Nom de famille">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                                <input type="text" name="prenom" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Prénom">
                            </div>
                        </div>

                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="exemple@email.com">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Ajouter l'adhérent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
