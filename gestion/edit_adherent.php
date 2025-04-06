<?php
session_start(); // Démarre ou reprend une session existante

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Configuration du dossier d'upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadProfil/'; // Définit le chemin du dossier d'upload
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Crée le dossier s'il n'existe pas avec les permissions appropriées
}

// Traitement du formulaire si la méthode est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $id = $_POST['id']; // Récupère l'ID de l'adhérent
    $nom = $_POST['nom']; // Récupère le nom de l'adhérent
    $prenom = $_POST['prenom']; // Récupère le prénom de l'adhérent
    $email = $_POST['email']; // Récupère l'email de l'adhérent
    $newPhoto = null; // Initialise la variable pour le nouveau chemin de la photo

    // Gestion de l'upload de la photo
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo']; // Récupère les informations du fichier uploadé
        
        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; // Définit les types de fichiers autorisés
        $maxSize = 2 * 1024 * 1024; // 2MB // Définit la taille maximale du fichier autorisée (2MB)

        // Vérification du type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // Ouvre un flux pour récupérer le type MIME
        $fileType = finfo_file($finfo, $file['tmp_name']); // Récupère le type MIME du fichier
        finfo_close($finfo); // Ferme le flux

        // Vérifie si le type de fichier est autorisé
        if (!in_array($fileType, $allowedTypes)) {
            die("Erreur : Type de fichier non autorisé (JPEG, PNG, GIF, WEBP uniquement)"); // Termine l'exécution du script si le type de fichier n'est pas autorisé
        }

        // Vérifie si la taille du fichier ne dépasse pas la limite autorisée
        if ($file['size'] > $maxSize) {
            die("Erreur : Le fichier est trop volumineux (max 2MB)"); // Termine l'exécution du script si la taille du fichier est trop grande
        }

        // Génération d'un nom unique pour le fichier
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION); // Récupère l'extension du fichier
        $filename = uniqid('img_') . '.' . $extension; // Génère un nom de fichier unique
        $destination = $uploadDir . $filename; // Définit le chemin de destination du fichier

        // Déplace le fichier uploadé vers le dossier de destination
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $newPhoto = '/BUT2/S4/Portofolio-Back/lib/uploadProfil/' . $filename; // Définit le chemin relatif du fichier pour la base de données
            
            // Suppression de l'ancienne photo si elle existe
            $oldPhotoQuery = $conn->prepare("SELECT profile_photo FROM adherents WHERE id = ?"); // Prépare la requête SQL
            $oldPhotoQuery->bind_param("i", $id); // Lie l'ID à la requête préparée
            $oldPhotoQuery->execute(); // Exécute la requête
            $oldPhotoQuery->bind_result($oldPhotoPath); // Lie le résultat à la variable $oldPhotoPath
            $oldPhotoQuery->fetch(); // Récupère le résultat
            $oldPhotoQuery->close(); // Ferme la requête

            // Vérifie si l'ancienne photo existe et la supprime
            if ($oldPhotoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPhotoPath)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldPhotoPath); // Supprime l'ancienne photo
            }
        } else {
            die("Erreur lors du téléversement du fichier."); // Termine l'exécution du script si le téléversement échoue
        }
    }

    // Préparation de la requête SQL pour mettre à jour les informations de l'adhérent
    if ($newPhoto) {
        $query = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ?, profile_photo = ? WHERE id = ?"); // Prépare la requête SQL avec la photo
        $query->bind_param("ssssi", $nom, $prenom, $email, $newPhoto, $id); // Lie les paramètres à la requête
    } else {
        $query = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ? WHERE id = ?"); // Prépare la requête SQL sans la photo
        $query->bind_param("sssi", $nom, $prenom, $email, $id); // Lie les paramètres à la requête
    }

    // Exécution de la requête
    if ($query->execute()) {
        header("Location: ../gestion/dashboard.php"); // Redirige vers le tableau de bord si la mise à jour réussit
    } else {
        echo "Erreur : " . $query->error; // Affiche un message d'erreur si la mise à jour échoue
    }

    $query->close(); // Ferme la requête
} else {
    // Récupération des informations de l'adhérent pour affichage dans le formulaire
    $id = $_GET['id']; // Récupère l'ID de l'adhérent depuis les paramètres GET
    $query = $conn->prepare("SELECT * FROM adherents WHERE id = ?"); // Prépare la requête SQL
    $query->bind_param("i", $id); // Lie l'ID à la requête préparée
    $query->execute(); // Exécute la requête
    $result = $query->get_result(); // Récupère le résultat
    $adherent = $result->fetch_assoc(); // Récupère les informations de l'adhérent sous forme de tableau associatif
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un adhérent</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Ajout d'un favicon pour améliorer l'identité visuelle -->
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <!-- Intégration de SweetAlert2 pour des alertes plus esthétiques (non utilisé ici) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-user-edit mr-2"></i>Modifier un adhérent
                </h2>

                <!-- Formulaire de modification d'adhérent -->
                <form action="edit_adherent.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Section pour la photo de profil -->
                    <div class="text-center">
                        <!-- Affichage de la photo actuelle -->
                        <img src="<?= htmlspecialchars($adherent['profile_photo'] ?? '../images/default-avatar.png') ?>" 
                            alt="Photo actuelle" 
                            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-purple-200">
                        <!-- Label pour changer la photo -->
                        <label class="cursor-pointer inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>
                            Changer la photo
                            <!-- Input de type fichier caché pour l'upload -->
                            <input type="file" 
                                name="profile_photo" 
                                class="hidden" 
                                accept="image/*">
                        </label>
                    </div>

                    <!-- Conteneur pour les champs nom, prénom et email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div>
                            <!-- Label pour le nom -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <!-- Input pour le nom -->
                            <input type="text" name="nom" 
                                value="<?= htmlspecialchars($adherent['nom']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Prénom -->
                        <div>
                            <!-- Label pour le prénom -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                            <!-- Input pour le prénom -->
                            <input type="text" name="prenom" 
                                value="<?= htmlspecialchars($adherent['prenom']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <!-- Label pour l'email -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <!-- Input pour l'email -->
                            <input type="email" name="email" 
                                value="<?= htmlspecialchars($adherent['email']) ?>" 
                                required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Champ caché pour l'ID de l'adhérent -->
                    <input type="hidden" name="id" value="<?= htmlspecialchars($adherent['id']) ?>">

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton pour enregistrer les modifications -->
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
$conn->close(); // Ferme la connexion à la base de données
?>
