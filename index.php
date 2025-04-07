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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css"/>
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">

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
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                    <div class="col-4 col-6-medium col-12-small">
                        <article class="box style2">
                            <!-- Titre de l'image -->
                            <h3 class="title"><?= htmlspecialchars($image['title']) ?></h3>

                            <!-- Image -->
                            <a href="lib/uploadPhoto/<?= htmlspecialchars($image['filename']) ?>"
                               class="image fit"
                               target="_blank"
                               title="Voir en grand">
                                <img src="lib/uploadPhoto/<?= htmlspecialchars($image['filename']) ?>"
                                     alt="<?= htmlspecialchars($image['filename']) ?>" />
                            </a>

                            <!-- Détails et actions -->
                            <div class="inner">
                                <p class="small">
                                    <strong>Uploadé le :</strong>
                                    <?= date('d/m/Y', strtotime($image['upload_date'])) ?>

                                    <?php if (isset($_SESSION['admin'])): ?>
                                        <br>
                                        <a href="gestion/edit_image.php?id=<?= $image['id'] ?>"
                                           class="icon solid fa-edit"
                                           style="color: #6c757d; margin-top: 0.5rem;">
                                        </a>
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
</section> -->

          
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

    <div class="flex flex-wrap justify-center gap-4 mx-auto max-w-6xl px-4 sm:px-6">
        <?php for($i = 1; $i <= 5; $i++): ?>
            <div class="text-center p-3 bg-white rounded-lg shadow w-full sm:w-auto sm:min-w-[150px] md:min-w-[180px]">
                <i class="<?= htmlspecialchars($home_content["second_stat{$i}_icon"] ?? 'fa-solid fa-code-branch') ?> text-3xl text-purple-600 mb-4"></i>
                <div class="font-bold text-xl mb-2"><?= htmlspecialchars($home_content["second_stat{$i}_number"] ?? '') ?></div>
                <div class="text-sm"><?= htmlspecialchars($home_content["second_stat{$i}_label"] ?? '') ?></div>
            </div>
        <?php endfor; ?>
    </div>

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
            <li style="list-style: none;"><a href="generic.php" class="button">Mon CV</a></li>

        </header>
        
<!-- Section Contact -->
<section class="main special">
    <header class="major">
        <h2>Me contacter</h2>
    </header>
    <div class="split style1">
        <section>
            <div id="message" class="hidden p-4 mb-4 rounded-lg text-center"></div>

            <form method="post" action="send_mail.php" id="contactForm" class="alt">
                <div class="row gtr-uniform" style="text-align: left;">
                    <?php foreach($fields as $field): ?>
                    <div class="<?= $field['field_type'] === 'select' ? 'col-12' : 'col-6 col-12-xsmall' ?>">
                        <label for="<?= $field['field_name'] ?>">
                            <?= htmlspecialchars($field['label']) ?>
                            <?= $field['is_required'] ? '<span class="text-red-500">*</span>' : '' ?>
                        </label>
                        
                        <?php if($field['field_type'] === 'select'): ?>
                            <select name="<?= $field['field_name'] ?>" 
                                    class="w-full p-2 border rounded">
                                <option value="" disabled selected>Choisir...</option>
                                <?php foreach(explode(',', $field['options']) as $option): ?>
                                <option value="<?= strtolower(trim($option)) ?>">
                                    <?= htmlspecialchars(trim($option)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif($field['field_type'] === 'textarea'): ?>
                            <textarea name="<?= $field['field_name'] ?>" 
                                      class="w-full p-2 border rounded"
                                      rows="5"
                                      placeholder="<?= htmlspecialchars($field['placeholder']) ?>"></textarea>
                        
                        <?php else: ?>
                            <input type="<?= $field['field_type'] ?>" 
                                   class="w-full p-2 border rounded"
                                   name="<?= $field['field_name'] ?>"
                                   placeholder="<?= htmlspecialchars($field['placeholder']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <div class="col-12 text-center mt-6">
                        <button type="submit" 
                                class="bg-purple-600 text-white px-1 py-1 rounded-lg hover:bg-purple-700 transition-all">
                            <i class="fas fa-paper-plane mr-2"></i>Envoyer
                        </button>
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

<script>
document.getElementById('contactForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const messageDiv = document.getElementById('message');
    const submitBtn = form.querySelector('button[type="submit"]');
    const progressBar = document.createElement('div');

    // Configuration initiale
    submitBtn.classList.add('cursor-wait');
    messageDiv.innerHTML = '';
    messageDiv.style.display = 'none';

    // Création de la barre de progression
    progressBar.className = 'h-1 bg-purple-600 absolute bottom-0 left-0';
    progressBar.style.width = '0%';
    messageDiv.appendChild(progressBar);

    // État de chargement
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="flex items-center justify-center">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Envoi en cours...
        </span>
    `;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        });

        if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
        
        const data = await response.json();
        
        // Animation du message
        messageDiv.innerHTML = `
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-3">
                    <i class="fas ${data.status === 'success' ? 'fa-check-circle' : 'fa-times-circle'} 
                        text-${data.status === 'success' ? 'green' : 'red'}-500 text-xl"></i>
                    <span>${data.message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.style.display = 'none'" 
                        class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        messageDiv.className = `rounded-lg shadow-lg mb-6 overflow-hidden ${
            data.status === 'success' 
            ? 'bg-green-50 border border-green-200' 
            : 'bg-red-50 border border-red-200'
        }`;
        
        messageDiv.style.display = 'block';
        messageDiv.animate([
            { opacity: 0, transform: 'translateY(-20px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], { duration: 300 });

        if(data.status === 'success') {
            form.reset();
            // Animation de la barre de progression
            progressBar.animate([
                { width: '0%' },
                { width: '100%' }
            ], { 
                duration: 5000,
                easing: 'linear'
            }).onfinish = () => {
                messageDiv.animate([
                    { opacity: 1 },
                    { opacity: 0 }
                ], { duration: 500 }).onfinish = () => {
                    messageDiv.style.display = 'none';
                };
            };
        }
    } catch (error) {
        console.error('Erreur:', error);
        messageDiv.innerHTML = `
            <div class="flex items-center p-4">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3 text-xl"></i>
                <span>${error.message || "Erreur lors de la communication avec le serveur"}</span>
            </div>
        `;
        messageDiv.className = 'bg-red-50 border border-red-200 rounded-lg shadow-lg mb-6';
        messageDiv.style.display = 'block';
        messageDiv.animate([
            { opacity: 0, transform: 'scale(0.95)' },
            { opacity: 1, transform: 'scale(1)' }
        ], { duration: 200 });
    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('cursor-wait');
        submitBtn.innerHTML = `
            <i class="fas fa-paper-plane mr-2"></i>Envoyer
        `;
    }
});
</script>
<script src="https://cdn.tailwindcss.com"></script>
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
		