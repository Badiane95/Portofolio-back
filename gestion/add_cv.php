<?php
session_start(); // Démarre ou reprend une session existante

require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Récupération et sanitisation des données du formulaire
        $nom_cv = htmlspecialchars($_POST['nom_cv']); // Nom du CV
        $description = htmlspecialchars($_POST['description']); // Description du CV
        $actif = isset($_POST['actif']) ? 1 : 0; // Statut actif du CV (1 si coché, 0 sinon)

        // Définition des chemins de stockage du fichier
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/upload/'; // Chemin absolu du répertoire d'upload
        $base_dir = $uploadDir; // Assure que $base_dir est défini
        $base_url = '/BUT2/S4/Portofolio-Back/lib/upload/'; // URL de base pour stocker dans la base de données

        // Vérification et création du répertoire si inexistant
        if (!file_exists($base_dir)) {
            if (!mkdir($base_dir, 0755, true)) { // Crée le répertoire avec les permissions appropriées
                throw new Exception("Impossible de créer le répertoire de stockage"); // Lance une exception si la création échoue
            }
        }

        // Validation du fichier uploadé
        $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION)); // Récupère l'extension du fichier et la convertit en minuscules
        $allowed_types = ['pdf', 'docx']; // Définit les types de fichiers autorisés

        // Vérifie si le type de fichier est autorisé
        if (!in_array($fileType, $allowed_types)) {
            throw new Exception("Seuls les formats PDF/DOCX sont autorisés"); // Lance une exception si le type de fichier n'est pas autorisé
        }

        // Vérifie si la taille du fichier ne dépasse pas la limite autorisée
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            throw new Exception("Taille maximale dépassée (5MB max)"); // Lance une exception si la taille du fichier est trop grande
        }

        // Génération d'un nom de fichier unique pour éviter les conflits
        $new_filename = uniqid('cv_') . '.' . $fileType; // Génère un ID unique pour le fichier
        $target_path = $base_dir . $new_filename; // Définit le chemin complet de destination du fichier

        // Déplacement du fichier uploadé vers le répertoire de stockage
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_path)) {
            throw new Exception("Erreur de déplacement du fichier. Vérifiez les permissions"); // Lance une exception si le déplacement échoue
        }

        // Insertion des informations du CV dans la base de données avec le chemin relatif
        $stmt = $conn->prepare("INSERT INTO cv (nom_cv, chemin_cv, description, actif) VALUES (?, ?, ?, ?)"); // Prépare la requête SQL
        $chemin_bdd = $base_url . $new_filename; // Utilise le chemin relatif pour stocker dans la base de données
        $stmt->bind_param("sssi", $nom_cv, $chemin_bdd, $description, $actif); // Lie les paramètres à la requête

        // Exécute la requête SQL
        if (!$stmt->execute()) {
            throw new Exception("Erreur base de données: " . $stmt->error); // Lance une exception si l'exécution de la requête échoue
        }

        $_SESSION['message'] = "CV uploadé avec succès"; // Enregistre un message de succès dans la session
        $stmt->close(); // Ferme la déclaration

    } catch (Exception $e) {
        // Gestion des erreurs : suppression du fichier uploadé en cas d'erreur et enregistrement du message d'erreur
        if (isset($target_path) && file_exists($target_path)) {
            unlink($target_path); // Supprime le fichier si l'upload a réussi mais que l'insertion en BDD a échoué
        }
        $_SESSION['error'] = $e->getMessage(); // Enregistre le message d'erreur dans la session
    } finally {
        $conn->close(); // Ferme la connexion à la base de données dans tous les cas
    }
}

header("Location: dashboard.php"); // Redirige vers le tableau de bord après le traitement du formulaire
exit; // Termine l'exécution du script
?>
