<?php
session_start();
include 'msql.php';

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
    <title>Modifier un adhérent</title>
    <link rel="stylesheet" href="assets/css/main.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">
    <div id="wrapper">
        <div id="main">
 <!-- Formulaire de modification d'un média social -->
 <section id="edit-social-media" class="main form-container">
                <h2>Modifier un média social</h2>
                <form method="POST" action="edit_social_media.php">
                    <div class="fields">
                        <div class="field">
                            <label for="nom">Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($media['nom']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="link">Lien</label>
                            <input type="url" name="link" value="<?= htmlspecialchars($media['link']) ?>" required>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="<?= $media['id'] ?>">
                    <ul class="actions special">
                        <li><button type="submit">Enregistrer</button></li>
                        </ul>
                </form>
            </section>
        </div>
    </div>

    <a href="dashboard.php" class="button primary">
    <i class="fas fa-arrow-left"></i> Retour au Dashboard
</a>

<?php
    include 'footer.php';?> 
    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
$conn->close();
?>
