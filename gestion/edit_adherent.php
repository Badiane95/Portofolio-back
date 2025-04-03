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
    $id = $_POST['id'];
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
            
            // Suppression de l'ancienne photo si elle existe
            $oldPhotoQuery = $conn->prepare("SELECT profile_photo FROM adherents WHERE id = ?");
            $oldPhotoQuery->bind_param("i", $id);
            $oldPhotoQuery->execute();
            $oldPhotoQuery->bind_result($oldPhotoPath);
            $oldPhotoQuery->fetch();
            $oldPhotoQuery->close();
            
            if ($oldPhotoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPhotoPath)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldPhotoPath);
            }
        } else {
            die("Erreur lors du téléversement du fichier.");
        }
    }

    // Préparation de la requête SQL
    if ($newPhoto) {
        $query = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ?, profile_photo = ? WHERE id = ?");
        $query->bind_param("ssssi", $nom, $prenom, $email, $newPhoto, $id);
    } else {
        $query = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ? WHERE id = ?");
        $query->bind_param("sssi", $nom, $prenom, $email, $id);
    }

    if ($query->execute()) {
        header("Location: ../gestion/dashboard.php");
    } else {
        echo "Erreur : " . $query->error;
    }

    $query->close();
} else {
    $id = $_GET['id'];
    $query = $conn->prepare("SELECT * FROM adherents WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $adherent = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un adhérent</title>
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
                    <h2 class="text-2xl font-bold text-purple-900 mb-6">Modifier un adhérent</h2>
                    <form action="edit_adherent.php" method="POST" enctype="multipart/form-data">
                        <div class="space-y-4">
                            <!-- Photo actuelle -->
                            <div class="text-center">
                                <img src="<?= htmlspecialchars($adherent['profile_photo'] ?? '../images/default-avatar.png') ?>" 
                                     alt="Photo actuelle" 
                                     class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-purple-100">
                                <label class="cursor-pointer inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Changer la photo
                                    <input type="file" 
                                           name="profile_photo" 
                                           class="hidden" 
                                           accept="image/*">
                                </label>
                            </div>
                            
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" id="nom" name="nom" 
                                       value="<?= htmlspecialchars($adherent['nom']) ?>" 
                                       required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input type="text" id="prenom" name="prenom" 
                                       value="<?= htmlspecialchars($adherent['prenom']) ?>" 
                                       required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?= htmlspecialchars($adherent['email']) ?>" 
                                       required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($adherent['id']) ?>">
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Modifier
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

<?php
$conn->close();
?>