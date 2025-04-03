<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Stellar by HTML5 UP</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body>

    <!-- Nav -->
    <nav id="nav">
        <ul>
            <?php
            // Inclut le fichier de connexion à la base de données
       include 'connexion/msql.php';


            // Vérifie si la connexion à la base de données est bien établie
            if ($conn) {
                // Récupération des éléments du menu triés par `order_num`
                $sql = "SELECT title, link FROM menu_items ORDER BY order_num";
                $result = $conn->query($sql);

                // Vérifie si des résultats existent
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<li><a href="' . htmlspecialchars($row['link']) . '">' . htmlspecialchars($row['title']) . '</a></li>';
                    }
                } else {
                    echo '<li><a href="#">Aucun élément de menu</a></li>';
                }

                // Ferme la connexion à la base de données
                $conn->close();
            } else {
                echo '<li><a href="#">Erreur de connexion</a></li>';
            }
            ?>
        </ul>
    </nav>

</body>
</html>
