<?php
 include 'msql.php'; 
 
$username = "admin";
$password = password_hash("monmotdepasse", PASSWORD_DEFAULT);

$query = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
$query->bind_param("ss", $username, $password);

if ($query->execute()) {
    echo "Administrateur ajouté avec succès.";
} else {
    echo "Erreur : " . $query->error;
}

$conn->close();
?>
