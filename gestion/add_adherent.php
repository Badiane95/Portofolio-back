<?php
session_start(); // Démarre ou reprend une session existante

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas administrateur
    exit; // Termine l'exécution du script
}

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Configuration du dossier d'upload
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadProfil/'; // Définit le chemin absolu du dossier d'upload
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Crée le dossier s'il n'existe pas avec les permissions appropriées
}

// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $nom = $_POST['nom']; // Nom de l'adhérent
    $prenom = $_POST['prenom']; // Prénom de l'adhérent
    $email = $_POST['email']; // Adresse email de l'adhérent
    $newPhoto = null; // Initialise la variable pour le chemin de la nouvelle photo

    // Vérification si l'adresse email existe déjà dans la base de données
    $checkQuery = $conn->prepare("SELECT COUNT(*) FROM adherents WHERE email = ?"); // Prépare la requête SQL
    $checkQuery->bind_param("s", $email); // Lie le paramètre à la requête
    $checkQuery->execute(); // Exécute la requête
    $checkQuery->bind_result($emailCount); // Lie le résultat à la variable $emailCount
    $checkQuery->fetch(); // Récupère le résultat
    $checkQuery->close(); // Ferme la requête

    // Si l'adresse email existe déjà, affiche un message d'erreur et termine l'exécution du script
    if ($emailCount > 0) {
        die("Erreur : L'adresse email est déjà utilisée."); // Termine l'exécution du script
    }

    // Gestion de l'upload de la photo de profil
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo']; // Récupère les informations du fichier uploadé

        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; // Définit les types de fichiers autorisés
        $maxSize = 2 * 1024 * 1024; // 2MB // Définit la taille maximale autorisée (2MB)

        // Vérification du type MIME réel du fichier
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // Ouvre un flux pour obtenir le type MIME
        $fileType = finfo_file($finfo, $file['tmp_name']); // Récupère le type MIME du fichier
        finfo_close($finfo); // Ferme le flux

        // Si le type de fichier n'est pas autorisé, affiche un message d'erreur et termine l'exécution du script
        if (!in_array($fileType, $allowedTypes)) {
            die("Erreur : Type de fichier non autorisé (JPEG, PNG, GIF, WEBP uniquement)"); // Termine l'exécution du script
        }

        // Si la taille du fichier dépasse la limite autorisée, affiche un message d'erreur et termine l'exécution du script
        if ($file['size'] > $maxSize) {
            die("Erreur : Le fichier est trop volumineux (max 2MB)"); // Termine l'exécution du script
        }

        // Génération d'un nom de fichier unique pour éviter les conflits
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION); // Récupère l'extension du fichier
        $filename = uniqid('img_') . '.' . $extension; // Génère un nom de fichier unique
        $destination = $uploadDir . $filename; // Définit le chemin de destination du fichier

        // Déplacement du fichier uploadé vers le dossier de destination
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $newPhoto = '/BUT2/S4/Portofolio-Back/lib/uploadProfil/' . $filename; // Définit le chemin relatif du fichier pour la base de données
        } else {
            die("Erreur lors du téléversement du fichier."); // Termine l'exécution du script
        }
    }

    // Préparation de la requête SQL pour insérer les données de l'adhérent dans la base de données
    $query = $conn->prepare("INSERT INTO adherents (nom, prenom, email, profile_photo) VALUES (?, ?, ?, ?)"); // Prépare la requête SQL
    $query->bind_param("ssss", $nom, $prenom, $email, $newPhoto); // Lie les paramètres à la requête

    // Exécution de la requête SQL
    if ($query->execute()) {
        header("Location: ../gestion/dashboard.php"); // Redirige vers le tableau de bord après l'ajout de l'adhérent
    } else {
        echo "Erreur : " . $query->error; // Affiche un message d'erreur si la requête échoue
    }

    $query->close(); // Ferme la requête
}

$conn->close(); // Ferme la connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un adhérent</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Ajout d'un favicon pour améliorer l'identité visuelle -->
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <!-- Intégration de SweetAlert2 pour des alertes plus esthétiques -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclut le menu de navigation ?>
    
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-user-plus mr-2"></i>Ajouter un adhérent
                </h2>

                <!-- Formulaire d'ajout d'adhérent -->
                <form action="add_adherent.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Section pour l'upload de la photo de profil -->
                    <div class="text-center">
                        <!-- Label cliquable pour l'upload de la photo -->
                        <label class="cursor-pointer inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>
                            Ajouter une photo
                            <!-- Input de type fichier caché pour l'upload -->
                            <input type="file" 
                                   name="profile_photo"
                                   class="hidden"
                                   accept="image/*">
                        </label>
                    </div>

                    <!-- Conteneur pour les champs nom, prénom et email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche pour le nom et le prénom -->
                        <div class="space-y-4">
                            <div>
                                <!-- Label pour le champ nom -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                                <!-- Input pour le champ nom -->
                                <input type="text" name="nom" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Nom de famille">
                            </div>

                            <div>
                                <!-- Label pour le champ prénom -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                                <!-- Input pour le champ prénom -->
                                <input type="text" name="prenom" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Prénom">
                            </div>
                        </div>

                        <!-- Colonne droite pour l'email -->
                        <div class="space-y-4">
                            <div>
                                <!-- Label pour le champ email -->
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <!-- Input pour le champ email -->
                                <input type="email" name="email" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="exemple@email.com">
                            </div>
                        </div>
                    </div>

                    <!-- Section pour le bouton d'ajout d'adhérent -->
                    <div class="flex justify-end border-t pt-6">
                        <!-- Bouton de soumission du formulaire -->
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
