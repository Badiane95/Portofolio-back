<?php
// Détails de connexion à la base de données
$host = "mysql-badiane.alwaysdata.net"; // L'adresse du serveur MySQL
$user = "badiane_13"; // Nom d'utilisateur MySQL
$password = "faloudu95**"; // Mot de passe MySQL
$database = "badiane_site"; // Nom de la base de données

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

?>

