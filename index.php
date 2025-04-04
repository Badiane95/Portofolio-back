<?php
include __DIR__ . '/connexion/msql.php';

// Récupération de toutes les données
$home_content = [];
$projects = [];
$images = [];
$fields = [];

// Contenu de la page d'accueil
$result_home = $conn->query("SELECT section_name, content FROM home_content");
if ($result_home->num_rows > 0) {
    while($row = $result_home->fetch_assoc()) {
        $home_content[$row['section_name']] = $row['content'];
    }
}

// Projets
$result_projects = $conn->query("SELECT * FROM projects LIMIT 3");
if ($result_projects) {
    while($row = $result_projects->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Images
$result_images = $conn->query("SELECT * FROM images ORDER BY upload_date DESC");
if ($result_images) {
    while($row = $result_images->fetch_assoc()) {
        $images[] = $row;
    }
}

// Formulaire de contact
$stmt = $conn->prepare("SELECT * FROM contact_form ORDER BY display_order ASC");
$stmt->execute();
$fields = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close(); // Fermeture de la connexion APRÈS toutes les requêtes
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Portfolio</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   

</head>
<body class="is-preload">

    <!-- Wrapper -->
    <div id="wrapper">

        <!-- Header -->
        <header id="header" class="alt">
            <span class="logo"><img src="images/logo.svg" alt="Logo" /></span>
            <h1><?= htmlspecialchars($home_content['header_title'] ?? 'Mon Portfolio') ?></h1>
            <p><?= htmlspecialchars($home_content['header_subtitle'] ?? 'Développeur Web Full-Stack') ?></p>
        </header>

        <!-- Nav -->
        <?php include 'nav.php'; ?>

        <!-- Main -->
        <div id="main">

            <!-- Section À propos -->
            <section id="intro" class="main special">
                <div class="spotlight">
                    <div class="content">
                        <header class="major">
                            <h2><?= htmlspecialchars($home_content['about_title'] ?? 'À propos') ?></h2>
                        </header>
                        <p><?= nl2br(htmlspecialchars($home_content['about_text'] ?? 'Développeur passionné avec 2 ans d\'expérience...')) ?></p>
                    </div>
                    <span class="image"><img src="images/gojo.png" alt="Profil" /></span>
                </div>
            </section>

           

            <!-- Section Galerie 
            <section id="gallery" class="main special">
                <header class="major">
                    <h2><?= htmlspecialchars($home_content['gallery_title'] ?? 'Galerie Photos') ?></h2>
                </header>
                
                <div class="box alt">
                    <div class="row gtr-uniform">
                        <?php if(!empty($images)): ?>
                            <?php foreach($images as $image): ?>
                                <div class="col-4 col-6-medium col-12-small">
                                    <article class="box style2">
                                        <a href="lib/uploadPhoto/<?= htmlspecialchars($image['filename']) ?>" 
                                           class="image fit" 
                                           target="_blank"
                                           title="Voir en grand">
                                            <img src="lib/uploadPhoto/<?= htmlspecialchars($image['filename']) ?>" 
                                                 alt="<?= htmlspecialchars($image['filename']) ?>" />
                                        </a>
                                        <div class="inner">
                                            <p class="small">
                                                <strong>Uploadé le :</strong> 
                                                <?= date('d/m/Y', strtotime($image['upload_date'])) ?>
                                                
                                                <?php if(isset($_SESSION['admin'])): ?>
                                                    <br>
                                                    <a href="gestion/delete_image.php?id=<?= $image['id'] ?>" 
                                                       class="icon solid fa-trash"
                                                       onclick="return confirm('Supprimer cette image définitivement ?')"
                                                       style="color: #e74c3c; margin-top: 0.5rem;">
                                                    </a>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p>Aucune image disponible pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            -->
            <!-- Section Introduction -->
            <section id="introduction" class="main">
                <div class="spotlight">
                    <div class="content">
                        <header class="major">
                            <h2><?= htmlspecialchars($home_content['intro_title'] ?? 'Ipsum sed adipiscing') ?></h2>
                        </header>
                        <p><?= nl2br(htmlspecialchars($home_content['intro_text'] ?? 'Sed lorem ipsum dolor sit amet nullam consequat...')) ?></p>
                        <ul class="actions">
                            <li><a href="Projets-front.php" class="button">Voir Projets</a></li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Section Compétences -->
            <section id="first" class="main special">
                <header class="major">
                    <h2><?= htmlspecialchars($home_content['first_title'] ?? 'Magna veroeros') ?></h2>
                </header>
                <ul class="features">
                    <li>
                        <span class="icon solid major style1 fa-code"></span>
                        <h3><?= htmlspecialchars($home_content['first_item1_title'] ?? 'Ipsum consequat') ?></h3>
                        <p><?= htmlspecialchars($home_content['first_item1_text'] ?? 'Sed lorem amet ipsum dolor et amet nullam consequat a feugiat consequat tempus veroeros sed consequat.') ?></p>
                    </li>
                    <li>
                        <span class="icon major style3 fa-copy"></span>
                        <h3><?= htmlspecialchars($home_content['first_item2_title'] ?? 'Amed sed feugiat') ?></h3>
                        <p><?= htmlspecialchars($home_content['first_item2_text'] ?? 'Sed lorem amet ipsum dolor et amet nullam consequat a feugiat consequat tempus veroeros sed consequat.') ?></p>
                    </li>
                    <li>
                        <span class="icon major style5 fa-gem"></span>
                        <h3><?= htmlspecialchars($home_content['first_item3_title'] ?? 'Dolor nullam') ?></h3>
                        <p><?= htmlspecialchars($home_content['first_item3_text'] ?? 'Sed lorem amet ipsum dolor et amet nullam consequat a feugiat consequat tempus veroeros sed consequat.') ?></p>
                    </li>
                </ul>
                <footer class="major">
                    <ul class="actions special">
                        <li><a href="elements.php" class="button">Voir plus</a></li>
                    </ul>
                </footer>
            </section>

           <!-- Section Statistiques -->
<section id="second" class="main special">
    <header class="major">
        <h2><?= htmlspecialchars($home_content['second_title'] ?? 'Ipsum consequat') ?></h2>
        <p><?= htmlspecialchars($home_content['second_text'] ?? 'Donec imperdiet consequat consequat. Suspendisse feugiat congue...') ?></p>
    </header>

    <ul class="statistics">
        <?php
        $default_icons = ['fa-solid fa-code-branch', 'fa-solid fa-folder-open', 'fa-solid fa-signal', 'fa-solid fa-laptop', 'fa-solid fa-gem'];
        $default_numbers = ['5,120', '8,192', '2,048', '4,096', '1,024'];
        $default_labels = ['Etiam', 'Magna', 'Tempus', 'Aliquam', 'Nullam'];

        for($i = 1; $i <= 5; $i++):
        ?>
            <li class="style<?= $i ?>">
                <span class="icon <?= htmlspecialchars($home_content["second_stat{$i}_icon"] ?? $default_icons[$i-1]) ?>"></span>
                <strong><?= htmlspecialchars($home_content["second_stat{$i}_number"] ?? $default_numbers[$i-1]) ?></strong>
                <?= htmlspecialchars($home_content["second_stat{$i}_label"] ?? $default_labels[$i-1]) ?>
            </li>
        <?php endfor; ?>
    </ul>

    <?php if(!empty($home_content['second_content'])): ?>
    <p class="content">
        <?= htmlspecialchars($home_content['second_content']) ?>
    </p>
    <?php endif; ?>

    <footer class="major">
        <?php if(!empty($home_content['second_button_text']) && !empty($home_content['second_button_link'])): ?>
        <ul class="actions special">
            <li>
                <a href="<?= htmlspecialchars($home_content['second_button_link']) ?>" class="button">
                    <?= htmlspecialchars($home_content['second_button_text']) ?>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </footer>
</section>

            <section id="cta" class="main special wrapper style1 fade-up">
    <div class="inner">
        <header class="major">
            <h2><?= htmlspecialchars($home_content['cta_title'] ?? 'Congue imperdiet') ?></h2>
            <p><?= htmlspecialchars($home_content['cta_text'] ?? 'Donec imperdiet consequat consequat. Suspendisse feugiat congue...') ?></p>
           <li class=button><a href="generic.php"> Mon Cv </a></li>
        </header>
 <!-- Section Contact -->
<section class="main special">
    <header class="major">
        <h2>Me contacter</h2>
    </header>
    <div class="split style1">
        <section>
            <form method="post" action="send_mail.php" id="contactForm" class="alt">
                <div class="row gtr-uniform" style="text-align: left;">
                    <?php foreach($fields as $field): 
                        $options = $field['options'] ? explode(',', $field['options']) : [];
                    ?>
                    <div class="<?= $field['field_type'] === 'select' ? 'col-12' : 'col-6 col-12-xsmall' ?>">
                        <label for="<?= $field['field_name'] ?>"><?= htmlspecialchars($field['label']) ?></label>
                        
                        <?php if($field['field_type'] === 'select'): ?>
                            <select name="<?= $field['field_name'] ?>" 
                                    id="<?= $field['field_name'] ?>" 
                                    <?= $field['is_required'] ? 'required' : '' ?> 
                                    style="width: 100%">
                                <option value="" disabled selected>Sélectionnez un type</option>
                                <?php foreach($options as $option): ?>
                                <option value="<?= strtolower($option) ?>"><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif($field['field_type'] === 'textarea'): ?>
                            <textarea name="<?= $field['field_name'] ?>" 
                                      id="<?= $field['field_name'] ?>" 
                                      rows="5" 
                                      placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                      <?= $field['is_required'] ? 'required' : '' ?> 
                                      style="width: 100%"></textarea>
                        
                        <?php else: ?>
                            <input type="<?= $field['field_type'] ?>" 
                                   name="<?= $field['field_name'] ?>" 
                                   id="<?= $field['field_name'] ?>" 
                                   placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                   <?= $field['is_required'] ? 'required' : '' ?> 
                                   style="width: 100%"/>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <!-- Bouton Submit -->
                    <div class="col-12" style="text-align: center;">
                        <ul class="actions">
                            <li><input type="submit" value="Envoyer" class="button primary" /></li>
                        </ul>
                    </div>
                </div>
            </form>
        </section>
    </div>
</section>

  
        </div>

       
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>

</body>

        <!-- Footer -->
        <footer id="footer">
    <section>
        <h2>Cordonneé</h2>
        <dl class="alt">
            <dt>Adresse</dt>
            <dd>Paris  &bull; France,</dd>
            <dt>Email</dt>
            <dd><a href="#">badiane.falou95@gmail.com</a></dd>
        </dl>

        <h2>Retrouver-moi</h2>
        <ul class="icons">
            <?php
            // Inclut le fichier de connexion à la base de données
            include __DIR__ . '/connexion/msql.php';

            // Vérifie si la connexion à la base de données est bien établie
            if ($conn) {
                // Prépare la requête SQL pour sélectionner les réseaux sociaux
                $sql = "SELECT nom, link FROM social_media";
                $result = $conn->query($sql);

                // Vérifie si des résultats existent
                if ($result && $result->num_rows > 0) {
                    // Parcourt chaque ligne de résultat
                    while ($row = $result->fetch_assoc()) {
                        echo '<li><a href="' . htmlspecialchars($row['link']) . '" target="_blank" class="icon brands alt fa-' . htmlspecialchars($row['nom']) . '">
                                <span class="label">' . ucfirst(htmlspecialchars($row['nom'])) . '</span>
                              </a></li>';
                    }
                } else {
                    echo "<li>Aucun réseau social trouvé.</li>";
                }

                // Ferme la connexion à la base de données
                $conn->close();
            } else {
                echo "<li>Erreur de connexion à la base de données.</li>";
            }
            ?>
        </ul>
    </section>

</footer>
</html>
		