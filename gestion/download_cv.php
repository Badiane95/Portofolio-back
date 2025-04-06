<?php
session_start(); // Démarre ou reprend une session existante

require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification des droits admin
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé"; // Enregistre un message d'erreur dans la session
    header("Location: ../login/session.php"); // Redirige vers la page de connexion
    exit; // Termine l'exécution du script
}

// Récupère l'ID du CV depuis les paramètres GET
$cv_id = $_GET['id'] ?? null;

// Vérifie si l'ID du CV est présent
if (!$cv_id) {
    $_SESSION['error'] = "ID de CV non spécifié"; // Enregistre un message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}

// Récupère le chemin du fichier et le nom du CV depuis la base de données
$query = "SELECT chemin_cv, nom_cv FROM cv WHERE id = ?";
$stmt = $conn->prepare($query); // Prépare la requête SQL
$stmt->bind_param("i", $cv_id); // Lie l'ID du CV à la requête préparée
$stmt->execute(); // Exécute la requête
$result = $stmt->get_result(); // Récupère le résultat de la requête
$cv = $result->fetch_assoc(); // Récupère la ligne de résultat sous forme de tableau associatif

// Vérifie si le CV a été trouvé dans la base de données
if (!$cv) {
    $_SESSION['error'] = "CV non trouvé"; // Enregistre un message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}

// Reconstitue le chemin complet du fichier
$file_path = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/upload/' . basename($cv['chemin_cv']);

// Vérifie si le fichier existe
if (!file_exists($file_path)) {
    $_SESSION['error'] = "Fichier non trouvé"; // Enregistre un message d'erreur dans la session
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit; // Termine l'exécution du script
}

// Définit les en-têtes pour forcer le téléchargement du fichier
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($cv['nom_cv']) . '"'); // Nom du fichier téléchargé
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path)); // Taille du fichier

// Nettoie le tampon de sortie
ob_clean();
flush();

// Lit le fichier et envoie son contenu au navigateur
readfile($file_path);

// Ferme la requête préparée
$stmt->close();
// Ferme la connexion à la base de données
$conn->close();
exit; // Termine l'exécution du script
?>
