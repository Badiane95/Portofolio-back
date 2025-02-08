<?php
session_start();

include 'msql.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("Veuillez remplir tous les champs.");
    }

    $query = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Nom d'utilisateur introuvable.";
    }

    $query->close();
}

$conn->close();
?>
