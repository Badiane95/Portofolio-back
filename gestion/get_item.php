<?php
// Démarrage de la session
session_start();

// Inclusion du fichier de connexion à la base de données
include __DIR__ . '/../connexion/msql.php';

// Définition du header pour une réponse JSON
header('Content-Type: application/json');

// Vérification de la présence des paramètres requis
if (!isset($_GET['category']) || !isset($_GET['id'])) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

// Récupération des paramètres GET
$category = $_GET['category'];
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Validation de l'ID comme entier

// Vérification de la validité de l'ID
if (!$id) {
    echo json_encode(['error' => 'ID invalide']);
    exit;
}

// Initialisation des variables pour la requête SQL
$table = '';
$columns = '*'; // Sélectionne toutes les colonnes par défaut

// Détermination de la table selon la catégorie
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

// Préparation de la requête SQL avec une requête préparée pour la sécurité
$stmt = $conn->prepare("SELECT {$columns} FROM {$table} WHERE id = ?");
$stmt->bind_param("i", $id); // Liaison du paramètre ID (entier)
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc(); // Récupération des résultats sous forme de tableau associatif

// Gestion du cas où l'élément n'est pas trouvé
if (!$item) {
    echo json_encode(['error' => 'Élément non trouvé']);
    exit;
}

// Renvoi des données au format JSON
echo json_encode($item);
?>
