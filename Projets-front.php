<?php
include __DIR__ . '/connexion/msql.php';

// Récupération de toutes les données
$projects = [];
$home_content = [];

// Contenu de la page d'accueil
$result_home = $conn->query("SELECT section_name, content FROM home_content");
if ($result_home->num_rows > 0) {
    while($row = $result_home->fetch_assoc()) {
        $home_content[$row['section_name']] = $row['content'];
    }
}

// Projets
$result_projects = $conn->query("SELECT * FROM projects LIMIT 3");
if ($result_projects && $result_projects->num_rows > 0) {
    while($row = $result_projects->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Récupération de la vidéo AVANT la fermeture de la connexion
$featured_video = $conn->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 1");
$video_data = $featured_video->num_rows > 0 ? $featured_video->fetch_assoc() : null;

$conn->close(); // On ferme la connexion APRÈS toutes les requêtes
?>
<!DOCTYPE HTML>
<html lang="fr" class="h-full">
<head>
    <title>Portfolio Badiane - Projets</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="container mx-auto px-4 py-12 flex-grow">
        <section id="projects" class="space-y-8">
            <header class="text-center mb-12">
                <h2 class="text-4xl font-bold text-purple-800 mb-4">
                    <?= htmlspecialchars($home_content['projects_title'] ?? 'Mes Projets') ?>
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Une sélection de mes projets récents, démontrant mes compétences et ma créativité en développement web.
                </p>
            </header>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($projects as $project): ?>
                    <div class="bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:scale-105">
                        <div class="relative">
                            <img 
                                src="<?= htmlspecialchars($project['image_path']) ?>" 
                                alt="<?= htmlspecialchars($project['name']) ?>" 
                                class="w-full h-56 object-cover"
                            />
                            <div class="absolute top-0 right-0 bg-purple-600 text-white px-4 py-2 rounded-bl-lg">
                                <?= htmlspecialchars(ucfirst($project['status'])) ?>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h3 class="text-2xl font-semibold text-purple-800 mb-3">
                                <?= htmlspecialchars($project['name']) ?>
                            </h3>
                            
                            <p class="text-gray-600 mb-4">
                                <?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...
                            </p>
                            
                            <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                                <span>
                                    <i class="fas fa-calendar-start text-purple-500 mr-2"></i>
                                    <?= date('d/m/Y', strtotime($project['start_date'])) ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar-end text-purple-500 mr-2"></i>
                                    <?= date('d/m/Y', strtotime($project['end_date'])) ?>
                                </span>
                            </div>
                            
                            <a 
                                href="gestion/project.php?id=<?= $project['id'] ?>" 
                                class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition duration-300"
                            >
                                Détails du Projet <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if(empty($projects)): ?>
                <div class="text-center bg-purple-100 border border-purple-300 text-purple-800 px-4 py-3 rounded">
                    Aucun projet n'est actuellement disponible.
                </div>
            <?php endif; ?>
        </section>
    </div>
    </div>

    <div class="container mx-auto px-4 py-4">
        <?php if($video_data): ?>
        <div class="bg-white rounded-xl shadow-xl p-6 mb-12 border-2 border-purple-100">
            <h2 class="text-3xl font-bold text-purple-700 mb-4"><?= htmlspecialchars($video_data['title']) ?></h2>
            <div class="aspect-w-16 aspect-h-9 mb-4">
                <iframe src="<?= htmlspecialchars($video_data['video_url']) ?>" 
                        class="w-full rounded-lg shadow-lg border border-purple-200"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
            </div>
            <p class="text-purple-600 text-lg"><?= htmlspecialchars($video_data['description']) ?></p>
        </div>
        <?php else: ?>
        <div class="bg-purple-50 p-8 rounded-xl text-center border-2 border-purple-200">
            <p class="text-purple-600 text-xl">Aucune vidéo disponible pour le moment</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="container mx-auto px-4 py-4">
        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
            <i class="fas fa-home mr-2"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>

