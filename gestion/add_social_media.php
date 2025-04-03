<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

// Vérification de la session
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $conn->real_escape_string($_POST['nom']);
    $link = $conn->real_escape_string($_POST['link']);

    $stmt = $conn->prepare("INSERT INTO social_media (nom, link) VALUES (?, ?)");
    $stmt->bind_param("ss", $nom, $link);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Média social ajouté avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout";
    }
    $stmt->close();
    
    // Redirection vers la même page pour afficher le message
    
    header("Location: add_social_media.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un média social</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold text-purple-600 mb-6">Ajouter un média social</h2>
        
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='text-green-600 mb-4'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo "<p class='text-red-600 mb-4'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        ?>
        
        <form action="add_social_media.php" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nom_media">
                    Nom
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nom_media" type="text" name="nom" placeholder="Nom du média social" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="link">
                    Lien
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="link" type="url" name="link" placeholder="Lien du média social" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</body>

</html>
