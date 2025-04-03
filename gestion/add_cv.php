<?php
session_start();
require __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Récupération des données
        $nom_cv = htmlspecialchars($_POST['nom_cv']);
        $description = htmlspecialchars($_POST['description']);
        $actif = isset($_POST['actif']) ? 1 : 0;

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/upload/';
        $base_dir = $uploadDir; // Ensure $base_dir is defined
        $base_url = '/BUT2/S4/Portofolio-Back/lib/upload/'; // Define the base URL for storing in the database


        // Vérification et création du répertoire
        if (!file_exists($base_dir)) {
            if (!mkdir($base_dir, 0755, true)) {
                throw new Exception("Impossible de créer le répertoire de stockage");
            }
        }

        // Validation du fichier
        $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['pdf', 'docx'];

        if (!in_array($fileType, $allowed_types)) {
            throw new Exception("Seuls les formats PDF/DOCX sont autorisés");
        }

        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            throw new Exception("Taille maximale dépassée (5MB max)");
        }

        // Génération d'un nom de fichier unique
        $new_filename = uniqid('cv_') . '.' . $fileType;
        $target_path = $base_dir . $new_filename;

        // Déplacement du fichier
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_path)) {
            throw new Exception("Erreur de déplacement du fichier. Vérifiez les permissions");
        }

        // Insertion en BDD avec chemin relatif
        $stmt = $conn->prepare("INSERT INTO cv (nom_cv, chemin_cv, description, actif) VALUES (?, ?, ?, ?)");
        $chemin_bdd = $base_url . $new_filename; // Utiliser le chemin relatif
        $stmt->bind_param("sssi", $nom_cv, $chemin_bdd, $description, $actif);

        if (!$stmt->execute()) {
            throw new Exception("Erreur base de données: " . $stmt->error);
        }

        $_SESSION['message'] = "CV uploadé avec succès";
        $stmt->close();

    } catch (Exception $e) {
        // Nettoyage en cas d'erreur
        if (isset($target_path) && file_exists($target_path)) {
            unlink($target_path);
        }
        $_SESSION['error'] = $e->getMessage();
    } finally {
        $conn->close();
    }
}

header("Location: dashboard.php");
exit;
?>
