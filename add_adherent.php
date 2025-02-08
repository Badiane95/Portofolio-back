<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: session.php");
    exit;
}

$host = "mysql-badiane.alwaysdata.net";
$user = "badiane_13";
$password = "faloudu95**";
$database = "badiane_site";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];

    $query = $conn->prepare("INSERT INTO adherents (nom, prenom, email) VALUES (?, ?, ?)");
    $query->bind_param("sss", $nom, $prenom, $email);

    if ($query->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Erreur : " . $query->error;
    }

    $query->close();
}

$conn->close();
?>
