<?php
include __DIR__ . '/connexion/msql.php';

// Récupération de toutes les données nécessaires pour la page

// Initialisation des tableaux pour stocker les données
$projects = []; // Tableau pour stocker les projets
$home_content = []; // Tableau pour stocker le contenu de la page d'accueil
$videos = []; // Tableau pour stocker les vidéos

// Récupération du contenu de la page d'accueil depuis la base de données
$result_home = $conn->query("SELECT section_name, content FROM home_content");

// Vérification si la requête a retourné des résultats
if ($result_home->num_rows > 0) {
    // Parcours de chaque ligne de résultat
    while($row = $result_home->fetch_assoc()) {
        // Assignation du contenu à la variable $home_content en utilisant le nom de la section comme clé
        $home_content[$row['section_name']] = $row['content'];
    }
}

// Récupération des projets depuis la base de données (limite à 6)
$result_projects = $conn->query("SELECT * FROM projects LIMIT 6");

// Vérification si la requête a retourné des résultats et si $result_projects est un objet valide
if ($result_projects && $result_projects->num_rows > 0) {
    // Parcours de chaque ligne de résultat
    while($row = $result_projects->fetch_assoc()) {
        // Ajout de chaque projet au tableau $projects
        $projects[] = $row;
    }
}

// Récupération de TOUTES les vidéos depuis la base de données, triées par date de création (la plus récente en premier)
$result_videos = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");

// Vérification si la requête a retourné des résultats et si $result_videos est un objet valide
if ($result_videos && $result_videos->num_rows > 0) {
    // Parcours de chaque ligne de résultat
    while($row = $result_videos->fetch_assoc()) {
        // Ajout de chaque vidéo au tableau $videos
        $videos[] = $row;
    }
}

// Fermeture de la connexion à la base de données pour libérer les ressources
$conn->close();
?>

<!DOCTYPE HTML>
<html lang="fr" class="h-full">
<head>
    <title>Portfolio Badiane - Projets</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Lien vers Tailwind CSS pour le styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lien vers Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Conteneur principal qui s'étend pour remplir l'espace disponible -->
    <div class="container mx-auto px-4 py-12 flex-grow">
        <!-- Section pour afficher les projets -->
        <section id="projects" class="space-y-8">
            <!-- En-tête de la section -->
            <header class="text-center mb-12">
                <!-- Titre de la section Projets -->
                <h2 class="text-4xl font-bold text-purple-800 mb-4">
                    <?= htmlspecialchars_decode($home_content['projects_title'] ?? 'Mes Projets', ENT_QUOTES) ?>
                </h2>
                <!-- Description de la section Projets -->
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Une sélection de mes projets récents, démontrant mes compétences et ma créativité en développement web.
                </p>
            </header>

            <!-- Grille pour afficher les projets (3 colonnes sur les écrans moyens et grands) -->
            <div class="grid md:grid-cols-3 gap-8">
                <?php if (!empty($projects)): ?>
                    <!-- Boucle à travers chaque projet -->
                    <?php foreach($projects as $project): ?>
                        <!-- Carte de projet individuelle -->
                        <div class="bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-105">
                            <!-- Conteneur relatif pour l'image et le badge de statut -->
                            <div class="relative">
                                <!-- Image du projet -->
                                <img 
                                    src="<?= htmlspecialchars($project['image_path']) ?>" 
                                    alt="<?= htmlspecialchars_decode($project['alt_text'], ENT_QUOTES) ?>" 
                                    class="w-full h-56 object-cover"
                                />
                                
                            </div>
                            
                            <!-- Conteneur pour le contenu du projet (nom, statut, description, dates) -->
                            <div class="p-6">
                            <div class="p-6">
                                <!-- Nom du projet -->
                                <h3 class="text-2xl font-semibold text-purple-800 mb-3">
                                <?= htmlspecialchars_decode(str_replace('&#039;', "'", $project['name'] ?? 'Nom non disponible'), ENT_QUOTES) ?>
                                </h3>
                                <!-- Statut du projet (dynamique en fonction de la valeur) -->
                                <span class="px-3 py-1 text-sm rounded-full
                                    <?php
                                    // Switch pour déterminer les classes CSS en fonction du statut du projet
                                    switch ($project['status'] ?? '') {
                                        case 'planned':
                                            echo 'bg-purple-100 text-purple-800'; // Style pour le statut "Planifié"
                                            $statusText = 'Planifié';
                                            break;
                                        case 'in_progress':
                                            echo 'bg-purple-200 text-purple-900'; // Style pour le statut "En cours"
                                            $statusText = 'En cours';
                                            break;
                                        case 'completed':
                                            echo 'bg-purple-600 text-white'; // Style pour le statut "Terminé"
                                            $statusText = 'Terminé';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800'; // Style par défaut pour un statut inconnu
                                            $statusText = 'Statut inconnu';
                                    }
                                    ?>">
                                    <?= $statusText ?>
                                </span>
                               </div>

                                
                                <!-- Description du projet (tronquée à 600 caractères) -->
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars_decode(substr($project['description'], 0, 600), ENT_QUOTES) ?>
                                </p>
                                
                                <!-- Conteneur pour afficher les dates de début et de fin du projet -->
                                <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    <!-- Date de début -->
                                    <span>
                                        <i class="fas fa-calendar-start text-purple-500 mr-2"></i>
                                        <?= date('d/m/Y', strtotime($project['start_date'])) ?>
                                    </span>
                                    <!-- Date de fin -->
                                    <span>
                                        <i class="fas fa-calendar-end text-purple-500 mr-2"></i>
                                        <?= date('d/m/Y', strtotime($project['end_date'])) ?>
                                    </span>
                                </div>
                                
                                <!-- Lien pour voir le projet -->
                                <a 
                                    href="<?= htmlspecialchars_decode($project['project_link'], ENT_QUOTES) ?>" 
                                    class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition duration-300"
                                    target="_blank" // Ouvrir le lien dans un nouvel onglet
                                >
                                    Voir le projet<i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message à afficher si aucun projet n'est disponible -->
                    <div class="text-center bg-purple-100 border border-purple-300 text-purple-800 px-4 py-3 rounded">
                        Aucun projet n'est actuellement disponible.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Conteneur principal qui s'étend pour remplir l'espace disponible -->
    <div class="container mx-auto px-4 py-12 flex-grow">
        <!-- Section pour afficher les vidéos -->
        <section id="videos" class="space-y-8">
            <!-- En-tête de la section Vidéos -->
            <header class="text-center mb-12">
                <!-- Titre de la section Vidéos -->
                <h2 class="text-4xl font-bold text-purple-800 mb-4">
                    Mes Vidéos
                </h2>
                <!-- Description de la section Vidéos -->
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Découvrez mes différenrt projet vidéo au cours de mes anné universitaire.
                </p>
            </header>

            <!-- Grille pour afficher les vidéos (2 colonnes sur les écrans moyens, 3 colonnes sur les écrans larges) -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if(!empty($videos)): ?>
                    <!-- Boucle à travers chaque vidéo -->
                    <?php foreach($videos as $video): 
                        // Extraction de l'ID de la vidéo YouTube à partir de l'URL
                        $url_components = parse_url($video['video_url']);
                        $path = trim($url_components['path'] ?? '', '/');
                        $video_id = basename(str_replace('/embed/', '', $path));
                        $youtube_url = "https://www.youtube.com/watch?v={$video_id}";
                    ?>
                        <!-- Carte de vidéo individuelle -->
                        <div class="bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-105">
                            <!-- Conteneur relatif pour maintenir l'aspect ratio de la vidéo -->
                            <div class="relative aspect-w-16 aspect-h-9">
                                <!-- Intégration de la vidéo YouTube -->
                                <iframe 
                                    src="<?= htmlspecialchars($video['video_url']) ?>" 
                                    class="w-full h-full object-cover"
                                    allowfullscreen> // Autoriser le plein écran
                                </iframe>
                                <!-- Overlay en bas de la vidéo avec un dégradé pour une meilleure lisibilité du titre -->
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-purple-900/60 p-4">
                                    <!-- Titre de la vidéo -->
                                    <h3 class="text-xl font-semibold text-white truncate">
                                        <?= htmlspecialchars_decode($video['title'], ENT_QUOTES) ?>
                                    </h3>
                                </div>
                            </div>
                            
                            <!-- Conteneur pour la description et les informations de la vidéo -->
                            <div class="p-6">
                                <!-- Description de la vidéo (tronquée à 600 caractères) -->
                                <p class="text-gray-600 mb-4">
                                    <?= htmlspecialchars_decode(substr($video['description'], 0, 600), ENT_QUOTES) ?>
                                </p>
                                
                                <!-- Date de création de la vidéo -->
                                <div class="flex items-center text-sm text-purple-600">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <?= date('d/m/Y', strtotime($video['created_at'])) ?>
                                </div>
                                
                                <!-- Lien pour voir la vidéo sur YouTube -->
                                <a 
                                    href="<?= htmlspecialchars($youtube_url) ?>" 
                                    target="_blank" // Ouvrir le lien dans un nouvel onglet
                                    class="mt-4 block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition duration-300"
                                >
                                    Voir sur YouTube <i class="fab fa-youtube ml-2"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message à afficher si aucune vidéo n'est disponible -->
                    <div class="col-span-3 text-center bg-purple-100 border border-purple-300 text-purple-800 px-4 py-3 rounded">
                        Aucune vidéo disponible pour le moment
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Conteneur pour le lien de retour à l'accueil -->
    <div class="container mx-auto px-4 py-4">
        <!-- Lien pour retourner à la page d'accueil -->
        <a 
            href="index.php" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
            <i class="fas fa-home mr-2"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>
