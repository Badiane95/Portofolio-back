<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $link = $conn->real_escape_string($_POST['link']);

    $stmt = $conn->prepare("UPDATE social_media SET nom=?, link=? WHERE id=?");
    $stmt->bind_param("ssi", $nom, $link, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Modification réussie";
    } else {
        $_SESSION['error'] = "Erreur de modification";
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

// Récupération des données existantes
$media = [];
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM social_media WHERE id = $id");
    $media = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un média social</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../script.js"></script>
</head>
<body class="bg-gray-100">
<?php
    include 'navback.php';?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
            <div class="md:flex">
                <div class="p-8 w-full">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Modifier un média social</h2>
                    <form method="POST" action="edit_social_media.php">
                        <div class="space-y-4">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($media['nom']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="link" class="block text-sm font-medium text-gray-700">Lien</label>
                                <input type="url" name="link" id="link" value="<?= htmlspecialchars($media['link']) ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?= $media['id'] ?>">
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="dashboard.php" class="inline-block bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-arrow-left mr-2"></i> Retour au Dashboard
            </a>
        </div>
    </div>

   

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/jquery.scrollex.min.js"></script>
    <script src="../assets/js/jquery.scrolly.min.js"></script>
    <script src="../assets/js/browser.min.js"></script>
    <script src="../assets/js/breakpoints.min.js"></script>
    <script src="../assets/js/util.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
$conn->close();
?>
