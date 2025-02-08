<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: session.php");
    exit;
}

echo "<h1>Bienvenue dans le tableau de bord, " . $_SESSION['admin'] . " !</h1>";
echo "<a href='logout.php'>Se déconnecter</a>";
?>
