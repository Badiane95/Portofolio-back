<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: session.php");
    exit;
}

include 'msql.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];

    $query = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ? WHERE id = ?");
    $query->bind_param("sssi", $nom, $prenom, $email, $id);

    if ($query->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Erreur : " . $query->error;
    }

    $query->close();
} else {
    $id = $_GET['id'];
    $query = $conn->prepare("SELECT * FROM adherents WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $adherent = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un adhérent</title>
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="shortcut icon" href="images/favicon.png" type="images/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="script.js"></script>
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">
    <div id="wrapper">
        <div id="main">
            <section id="edit-adherent" class="main">
                <header class="major">
                    <h2>Modifier un adhérent</h2>
                </header>
                <form action="edit_adherent.php" method="POST">
                    <div class="fields">
                        <div class="field">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($adherent['nom']); ?>" required />
                        </div>
                        <div class="field">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($adherent['prenom']); ?>" required />
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adherent['email']); ?>" required />
                        </div>
                    </div>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($adherent['id']); ?>">
                    <ul class="actions special">
                        <li><button type="submit" class="button primary">Modifier</button></li>
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
