<?php
include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données.

// Définition du nom d'utilisateur par défaut.  IMPORTANT :  Ne pas laisser en dur dans un environnement de production.  Utiliser une variable d'environnement ou un fichier de configuration sécurisé.
$username = "admin";

// Hashage du mot de passe. Utilisation de password_hash pour une sécurité maximale.
// IMPORTANT : Le coût par défaut de password_hash est suffisant pour la plupart des applications.  L'augmenter considérablement peut provoquer des lenteurs.
$password = password_hash("faloudu95", PASSWORD_DEFAULT);

// Préparation de la requête pour vérifier si l'utilisateur existe déjà.
// Utilisation de requêtes préparées pour éviter les injections SQL.
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
// Liaison du paramètre username à la requête préparée.
$stmt->bind_param("s", $username);
// Exécution de la requête préparée.
$stmt->execute();
// Récupération du résultat de la requête.
$result = $stmt->get_result();

// Vérification du nombre de lignes retournées par la requête.
if ($result->num_rows > 0) {
    // L'utilisateur existe déjà.

    // Préparation de la requête de mise à jour du mot de passe.
    $update = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
    // Liaison des paramètres password et username à la requête préparée.
    $update->bind_param("ss", $password, $username);
    // Exécution de la requête préparée.
    $update->execute();
    // Affichage d'un message de succès.
    echo "Mot de passe de l'administrateur mis à jour.";
} else {
    // L'utilisateur n'existe pas.

    // Préparation de la requête d'insertion d'un nouvel utilisateur.
    $insert = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    // Liaison des paramètres username et password à la requête préparée.
    $insert->bind_param("ss", $username, $password);
    // Exécution de la requête préparée.
    $insert->execute();
    // Affichage d'un message de succès.
    echo "Nouvel administrateur ajouté.";
}

// Fermeture de la connexion à la base de données.
$conn->close();
?>
