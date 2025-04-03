<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

echo "<h1>Bienvenue dans le tableau de bord, " . $_SESSION['admin'] . " !</h1>";
echo "<a href='../login/logout.php'>Se déconnecter</a>";
?>
