<?php
session_start();
session_destroy();
header("Location: ../index.php"); // Redirige vers la page d'accueil
exit;
?>
