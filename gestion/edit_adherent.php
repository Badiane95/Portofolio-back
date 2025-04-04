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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-user-edit mr-2"></i>Modifier un adhérent
                </h2>

                <form action="edit_adherent.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Photo de profil -->
                    <div class="text-center">
                        <img src="<?= htmlspecialchars($adherent['profile_photo'] ?? '../images/default-avatar.png') ?>" 
                            alt="Photo actuelle" 
                            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-purple-200">
                        <label class="cursor-pointer inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>
                            Changer la photo
                            <input type="file" 
                                name="profile_photo" 
                                class="hidden" 
                                accept="image/*">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <input type="text" name="nom" 
                                value="<?= htmlspecialchars($adherent['nom']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Prénom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                            <input type="text" name="prenom" 
                                value="<?= htmlspecialchars($adherent['prenom']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" 
                                value="<?= htmlspecialchars($adherent['email']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <input type="hidden" name="id" value="<?= htmlspecialchars($adherent['id']) ?>">

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                        </button>
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