<?php
include __DIR__ . '/../connexion/msql.php';

$username = "admin";
$password = password_hash("faloudu95", PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // L'utilisateur existe, mettre à jour le mot de passe
    $update = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
    $update->bind_param("ss", $password, $username);
    $update->execute();
    echo "Mot de passe de l'administrateur mis à jour.";
} else {
    // L'utilisateur n'existe pas, l'insérer
    $insert = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    $insert->bind_param("ss", $username, $password);
    $insert->execute();
    echo "Nouvel administrateur ajouté.";
}

$conn->close();
?>
