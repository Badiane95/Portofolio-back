<?php
session_start();
require __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php");
    exit;
}

$cv_id = $_GET['id'] ?? null;

if (!$cv_id) {
    $_SESSION['error'] = "ID de CV non spécifié";
    header("Location: dashboard.php");
    exit;
}

// Fetch the file path from the database
$query = "SELECT chemin_cv, nom_cv FROM cv WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cv_id);
$stmt->execute();
$result = $stmt->get_result();
$cv = $result->fetch_assoc();

if (!$cv) {
    $_SESSION['error'] = "CV non trouvé";
    header("Location: dashboard.php");
    exit;
}

$file_path = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/upload/' . basename($cv['chemin_cv']);

// Check if the file exists
if (!file_exists($file_path)) {
    $_SESSION['error'] = "Fichier non trouvé";
    header("Location: dashboard.php");
    exit;
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($cv['nom_cv']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Clear output buffer
ob_clean();
flush();

// Read the file and output its contents
readfile($file_path);

// Close the connection
$stmt->close();
$conn->close();
exit;
?>
