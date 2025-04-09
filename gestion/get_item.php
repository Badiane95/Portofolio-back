<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

header('Content-Type: application/json');

if (!isset($_GET['category']) || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

$category = $_GET['category'];
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['error' => 'ID invalide']);
    exit;
}

$table = '';
$columns = '*';

switch ($category) {
    case 'projects':
        $table = 'projects';
        break;
    case 'adherents':
        $table = 'adherents';
        break;
    case 'images':
        $table = 'images';
        break;
    case 'skills':
        $table = 'skills';
        break;
    default:
        echo json_encode(['error' => 'Catégorie invalide']);
        exit;
}

$stmt = $conn->prepare("SELECT {$columns} FROM {$table} WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo json_encode(['error' => 'Élément non trouvé']);
    exit;
}

echo json_encode($item);
?>
