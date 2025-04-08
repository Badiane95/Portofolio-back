<?php
session_start();

// Redirection vers le login si non authentifié
if (!isset($_SESSION['admin'])) {
    header("Location: login/session.php"); // Chemin corrigé
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <style>
        .admin-header {
            background: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            background: #d9534f;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h2>Bienvenue <?php echo htmlspecialchars($_SESSION['admin']); ?></h2>
        <nav>
            <a href="login/logout.php">Déconnexion</a> <!-- Chemin corrigé -->
        </nav>
    </header>