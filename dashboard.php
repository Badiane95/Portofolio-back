<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: session.php");
    exit;
}

if (isset($_SESSION['message'])) {
    echo "<p class='success'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo "<p class='error'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}

include 'msql.php';

// Récupérer la liste des adhérents
$query_adherents = "SELECT * FROM adherents";
$result_adherents = $conn->query($query_adherents);

// Récupérer la liste des médias sociaux
$query_social_media = "SELECT * FROM social_media";
$result_social_media = $conn->query($query_social_media);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="assets/css/main.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="images/favicon.png" type="images/png">
    <script src="script.js"></script>
</head>
<body>
    <div id="main">
        <section id="admin-dashboard" class="main">
            <header class="major">
                <h1>Bienvenue dans le tableau de bord, <?php echo $_SESSION['admin']; ?> !</h1>
            </header>
            <ul class="actions">
                <li><a href='logout.php' class="button">Se déconnecter</a></li>
            </ul>

            <h2>Gestion des adhérents</h2>
            
            <h3>Ajouter un adhérent</h3>
            <form action="add_adherent.php" method="POST">
                <div class="fields">
                    <div class="field">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" placeholder="Nom" required />
                    </div>
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" placeholder="Prénom" required />
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Email" required />
                    </div>
                </div>
                <ul class="actions">
                    <li><input type="submit" value="Ajouter" class="primary" /></li>
                </ul>
            </form>

            <h3>Liste des adhérents</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_adherents->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nom']; ?></td>
                            <td><?php echo $row['prenom']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <ul class="actions small">
                                    <li><a href="edit_adherent.php?id=<?php echo $row['id']; ?>" class="button small">Modifier</a></li>
                                    <li><a href="delete_adherent.php?id=<?php echo $row['id']; ?>" class="button small" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?');">Supprimer</a></li>
                                </ul>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h2>Gestion des médias sociaux</h2>
            
            <h3>Ajouter un média social</h3>
<form action="add_social_media.php" method="POST">
    <div class="fields">
        <div class="field">
            <label for="nom_media">Nom</label>
            <input type="text" name="nom" id="nom_media" placeholder="Nom du média social" required />
        </div>
        <div class="field">
            <label for="link">Lien</label>
            <input type="url" name="link" id="link" placeholder="Lien du média social" required />
        </div>
    </div>
    <ul class="actions">
        <li><input type="submit" value="Ajouter" class="primary" /></li>
    </ul>
</form>

<h3>Liste des médias sociaux</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Lien</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result_social_media->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['nom']; ?></td>
                <td><?php echo $row['link']; ?></td>
                <td>
                    <ul class="actions small">
                        <li><a href="edit_social_media.php?id=<?php echo $row['id']; ?>" class="button small">Modifier</a></li>
                        <li><a href="delete_social_media.php?id=<?php echo $row['id']; ?>" class="button small" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce média social ?');">Supprimer</a></li>
                    </ul>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
        </section>
    </div>
    <?php
    include 'footer.php';?> 
</body>
</html>

<?php
$conn->close();
?>
