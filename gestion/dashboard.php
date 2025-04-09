<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/session.php");
    exit;
}

// Affichage des messages et erreurs
if (isset($_SESSION['message'])) {
    echo "<p class='success'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo "<p class='error'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}

include __DIR__ . '/../connexion/msql.php';

// Récupérer la liste des projets

$query_projects = "SELECT * FROM projects";
$result_projects = $conn->query($query_projects);

// Récupérer la liste des adhérents
$query_adherents = "SELECT * FROM adherents";
$result_adherents = $conn->query($query_adherents);

// Récupérer la liste des médias sociaux
$query_social_media = "SELECT * FROM social_media";
$result_social_media = $conn->query($query_social_media);

// Récupérer la liste des images
$query_images = "SELECT * FROM images ORDER BY upload_date DESC";
$result_images = $conn->query($query_images);

// Récupérer le contenu existant pour la page d'accueil
$query_home_content = "SELECT * FROM home_content";
$result_home_content = $conn->query($query_home_content);
$home_data = [];
while ($row = $result_home_content->fetch_assoc()) {
    $home_data[$row['section_name']] = $row['content'];

    // Also load the new competence fields.
    $home_data['first_item1_icon'] = $row['first_item1_icon'];
    $home_data['first_item1_title'] = $row['first_item1_title'];
    $home_data['first_item1_text'] = $row['first_item1_text'];
    $home_data['first_item2_icon'] = $row['first_item2_icon'];
    $home_data['first_item2_title'] = $row['first_item2_title'];
    $home_data['first_item2_text'] = $row['first_item2_text'];
    $home_data['first_item3_icon'] = $row['first_item3_icon'];
    $home_data['first_item3_title'] = $row['first_item3_title'];
    $home_data['first_item3_text'] = $row['first_item3_text'];
}


// Suppression
if (isset($_GET['delete'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = "Compétence supprimée !";
}

// Récupération des compétences
$result = $conn->query("SELECT * FROM skills ORDER BY created_at DESC");
$skills = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-purple-800 mb-6">
            <i class="fas fa-tachometer-alt mr-2"></i>Bienvenue, <?php echo $_SESSION['admin']; ?> !
        </h1>

       <!-- Liste des projets -->
<section class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-purple-800">
            <i class="fas fa-project-diagram mr-2"></i>Projets en cours
        </h2>
    </div>

    <div class="bg-white shadow-xl rounded-lg border border-purple-100">
        <?php if ($result_projects && $result_projects->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                <?php while($project = $result_projects->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-[1.02]">
                        <img src="<?= htmlspecialchars($project['image_path'] ?? '') ?>" 
                             alt="<?= htmlspecialchars($project['alt_text'] ?? 'Image du projet') ?>" 
                             class="w-full h-48 object-cover">
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-purple-800">
                                    <?= htmlspecialchars_decode($project['name'] ?? 'Nom non disponible', ENT_QUOTES) ?>
                                </h3>
                                <span class="px-3 py-1 text-sm rounded-full 
                                    <?= match($project['status'] ?? '') {
                                        'planned' => 'bg-purple-100 text-purple-800',
                                        'in_progress' => 'bg-purple-200 text-purple-900',
                                        'completed' => 'bg-purple-600 text-white',
                                        default => 'bg-gray-100 text-gray-800'
                                    } ?>">
                                    <?= match($project['status'] ?? '') {
                                        'planned' => 'Planifié',
                                        'in_progress' => 'En cours',
                                        'completed' => 'Terminé',
                                        default => 'Statut inconnu'
                                    } ?>
                                </span>
                            </div>

                            <?php if(!empty($project['description'])): ?>
                            <p class="text-gray-600 mb-4 text-sm">
                                <?= htmlspecialchars_decode($project['description'] ?? '', ENT_QUOTES) ?>
                            </p>
                            <?php endif; ?>

                            <div class="text-sm text-purple-600 space-y-2 mb-4">
                                <?php if(!empty($project['start_date'])): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-start mr-2"></i>
                                    <?= date('d/m/Y', strtotime($project['start_date'])) ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($project['end_date'])): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-end mr-2"></i>
                                    <?= date('d/m/Y', strtotime($project['end_date'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-end space-x-3">
                                <a href="edit_project.php?id=<?= (int)($project['id'] ?? 0) ?>" 
                                   class="text-purple-600 hover:text-purple-900 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Modifier
                                </a>
                                <a href="delete_project.php?id=<?= (int)($project['id'] ?? 0) ?>" 
                                   class="text-red-600 hover:text-red-900 text-sm"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center bg-purple-50 border-t border-purple-100">
                <p class="text-purple-700">Aucun projet n'est actuellement enregistré</p>
            </div>
        <?php endif; ?>
    </div>
</section>

    
<section>
 <!-- Section Vidéos -->
<section class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-purple-800">
            <i class="fas fa-video mr-2"></i>Vidéos publiées
        </h2>
    </div>

    <div class="bg-white shadow-xl rounded-lg border border-purple-100">
        <?php
        $videos = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
        if ($videos->num_rows > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
                <?php while($video = $videos->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-[1.01]">
                        <div class="aspect-video bg-gray-100">
                            <iframe src="<?= htmlspecialchars($video['video_url']) ?>" 
                                class="w-full h-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                            </iframe>
                        </div>
                        
                        <div class="p-6">
    <div class="flex justify-between items-start mb-4">
        <h3 class="text-xl font-semibold text-purple-800 truncate">
            <?= htmlspecialchars_decode($video['title'] ?? '', ENT_QUOTES) ?>
        </h3>
        <span class="text-sm text-purple-600">
            <?= date('d/m/Y', strtotime($video['created_at'])) ?>
        </span>
    </div>

                            
    <?php if(!empty($video['description'])): ?>
    <p class="text-gray-600 mb-4 text-sm">
        <?= htmlspecialchars_decode($video['description'] ?? '', ENT_QUOTES) ?>
    </p>
    <?php endif; ?>

                            <div class="flex items-center justify-end space-x-3">
                                <a href="edit_video.php?id=<?= $video['id'] ?>" 
                                   class="text-purple-600 hover:text-purple-900 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Modifier
                                </a>
                                <a href="delete_video.php?id=<?= $video['id'] ?>" 
                                   class="text-red-600 hover:text-red-900 text-sm"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center bg-purple-50 border-t border-purple-100">
                <p class="text-purple-700">Aucune vidéo publiée pour le moment</p>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- Section Gestion des Champs du Formulaire -->
<section class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-purple-800">Gestion des Champs du Formulaire</h2>
    </div>

    <div class="bg-white shadow-xl rounded-lg border border-purple-100">
        <?php
        $fields = $conn->query("SELECT * FROM contact_form ORDER BY display_order ASC");
        if ($fields->num_rows > 0): ?>
            <div class="divide-y divide-gray-200">
                <!-- En-têtes du tableau -->
                <div class="hidden md:grid grid-cols-12 gap-4 bg-gray-50 px-6 py-3 text-sm font-medium text-gray-500">
                    <div class="col-span-2">Nom du champ</div>
                    <div class="col-span-2">Type</div>
                    <div class="col-span-3">Libellé</div>
                    <div class="col-span-2">Ordre</div>
                    <div class="col-span-3">Actions</div>
                </div>

                <?php while($field = $fields->fetch_assoc()): ?>
                    <div class="group hover:bg-gray-50 transition-colors px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                            <!-- Nom du champ -->
                            <div class="col-span-2 font-medium text-gray-900">
                                <?= htmlspecialchars($field['field_name']) ?>
                            </div>

                            <!-- Type de champ -->
                            <div class="col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= match($field['field_type']) {
                                        'text' => 'bg-blue-100 text-blue-800',
                                        'email' => 'bg-green-100 text-green-800',
                                        'select' => 'bg-purple-100 text-purple-800',
                                        'textarea' => 'bg-yellow-100 text-yellow-800'
                                    } ?>">
                                    <?= ucfirst($field['field_type']) ?>
                                </span>
                            </div>

                            <!-- Libellé -->
                            <div class="col-span-3 text-gray-600">
                                <?= htmlspecialchars($field['label']) ?>
                            </div>

                            <!-- Ordre d'affichage -->
                            <div class="col-span-2">
                                <form method="POST" action="edit_field.php" class="flex items-center gap-2">
                                    <input type="number" name="display_order" value="<?= $field['display_order'] ?>" 
                                           class="w-16 px-2 py-1 border rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500" min="1">
                                    <input type="hidden" name="id" value="<?= $field['id'] ?>">
                                    <button type="submit" name="update_order" 
                                            class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Actions -->
                            <div class="col-span-3 flex items-center gap-4">
                                <a href="edit_field.php?id=<?= $field['id'] ?>" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                   <i class="fas fa-edit mr-1"></i>Modifier
                                </a>
                                <span class="text-gray-300">|</span>
                                <form method="POST" action="delete_field.php" onsubmit="return confirm('Êtes-vous sûr ?')">
                                    <input type="hidden" name="id" value="<?= $field['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-gray-500">Aucun champ configuré</div>
        <?php endif; ?>
    </div>
</section>
<section> 
            <!-- Section de gestion du contenu de la page d'accueil -->
            <h2 class="text-2xl font-bold text-purple-700 mb-6 border-l-4 border-purple-500 pl-4">Gestion du contenu principal</h2>
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-purple-50">
              

                <form method="POST" action="update_content.php" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Section Header -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">En-tête</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="header_title" id="header_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       value="<?= htmlspecialchars($home_data['header_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Sous-titre</label>
                                <input type="text" name="header_subtitle" id="header_subtitle" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['header_subtitle'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Introduction -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Introduction</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="intro_title" id="intro_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['intro_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="intro_text" id="intro_text" rows="3"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['intro_text'] ?? '') ?></textarea>
                            </div>
                        </div>
                      <!-- Section  Compétences-->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
    <h3 class="text-lg font-semibold text-purple-600">Section Compétences</h3>

    <!-- Prévisualisation en temps réel -->
    <div class="mb-6 p-4 border border-purple-200 rounded-lg">
        <h4 class="text-md font-semibold text-purple-600 mb-2">Aperçu</h4>
        <div class="grid grid-cols-3 gap-4">
            <?php for($i = 1; $i <= 3; $i++): ?>
            <div class="text-center p-3 bg-white rounded-lg shadow">
                <i class="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?> text-3xl text-purple-600 mb-2"></i>
                <div class="font-bold text-xl"><?= htmlspecialchars($home_data["second_stat{$i}_title"] ?? '') ?></div>
                <div class="text-sm"><?= htmlspecialchars($home_data["first_item{$i}_title"] ?? '') ?></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
        <input type="text" name="first_title" id="first_title"
               class="w-full p-2 border border-purple-200 rounded-md"
               value="<?= htmlspecialchars($home_data['first_title'] ?? 'Titre principal') ?>">
    </div>

    <!-- Champs pour les éléments avec icônes -->
    <?php for($i = 1; $i <= 3; $i++): ?>
    <div class="space-y-2 pl-4 border-l-2 border-purple-200 bg-white p-4 rounded-lg shadow-sm">
        <label class="block text-sm font-medium text-purple-700">Élément <?= $i ?></label>

        <div class="flex items-center mb-3">
            <span class="text-purple-600 mr-2">Icône:</span>
            <input type="text"
                   name="first_item<?= $i ?>_icon"
                   class="flex-1 p-2 border-b-2 border-purple-100 focus:border-purple-500"
                   placeholder="Classe Font Awesome (ex: fa-solid fa-star)"
                   value="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?>">
        </div>

        <div class="grid grid-cols-1 gap-4">
            <input type="text"
                   name="first_item<?= $i ?>_title"
                   placeholder="Titre"
                   class="w-full p-2 border border-purple-100 rounded-md"
                   value="<?= htmlspecialchars($home_data["second_stat{$i}_title"] ?? '') ?>">

            <input type="text"
                   name="first_item<?= $i ?>_text"
                   placeholder="Texte"
                   class="w-full p-2 border border-purple-100 rounded-md"
                   value="<?= htmlspecialchars($home_data["first_item{$i}_text"] ?? '') ?>">
        </div>
    </div>
    <?php endfor; ?>
</div>


                        <!-- Section À propos -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section À propos</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="about_title" id="about_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['about_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="about_text" id="about_text" rows="3"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['about_text'] ?? '') ?></textarea>
                            </div>
                            
                        </div>

                        <!-- Section Projets -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Projets</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="projects_title" id="projects_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['projects_title'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Galerie -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Galerie</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="gallery_title" id="gallery_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['gallery_title'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Seconde Section -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Seconde Section</h3>

                            <!-- Titre et Texte -->
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="second_title" class="w-full p-2 border border-purple-200 rounded-md" 
                                       value="<?= htmlspecialchars($home_data['second_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte d'introduction</label>
                                <textarea name="second_text" rows="2" class="w-full p-2 border border-purple-200 rounded-md"><?= 
                                    htmlspecialchars($home_data['second_text'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Section Statistiques -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Statistiques</h3>

                            <!-- Prévisualisation en temps réel -->
                            <div class="mb-6 p-4 border border-purple-200 rounded-lg">
                                <h4 class="text-md font-semibold text-purple-600 mb-2">Aperçu</h4>
                                <div class="grid grid-cols-5 gap-4">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <div class="text-center p-3 bg-white rounded-lg shadow">
                                        <i class="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?> text-3xl text-purple-600 mb-2"></i>
                                        <div class="font-bold text-xl"><?= htmlspecialchars($home_data["second_stat{$i}_number"] ?? '') ?></div>
                                        <div class="text-sm"><?= htmlspecialchars($home_data["second_stat{$i}_label"] ?? '') ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Champs de formulaire améliorés -->
                            <?php for($i = 1; $i <= 5; $i++): ?>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <div class="flex items-center mb-3">
                                    <span class="text-purple-600 mr-2">#<?= $i ?></span>
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_icon" 
                                           class="flex-1 p-2 border-b-2 border-purple-100 focus:border-purple-500" 
                                           placeholder="Classe Font Awesome (ex: fa-users)"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?>">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_number" 
                                           class="p-2 border rounded-lg" 
                                           placeholder="Nombre"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_number"] ?? '') ?>">
                                    
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_label" 
                                           class="p-2 border rounded-lg" 
                                           placeholder="Libellé"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_label"] ?? '') ?>">
                                </div>
                            </div>
                            <?php endfor; ?>

                            <!-- Contenu principal -->
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Contenu détaillé</label>
                                <textarea name="second_content" rows="5" class="w-full p-2 border border-purple-200 rounded-md"><?= 
                                    htmlspecialchars($home_data['second_content'] ?? '') ?></textarea>
                            </div>

                            <!-- Bouton -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-purple-700 mb-1">Texte du bouton</label>
                                    <input type="text" name="second_button_text" class="w-full p-2 border border-purple-200 rounded-md" 
                                           value="<?= htmlspecialchars($home_data['second_button_text'] ?? '') ?>">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-purple-700 mb-1">Lien du bouton</label>
                                    <input type="url" name="second_button_link" class="w-full p-2 border border-purple-200 rounded-md" 
                                           value="<?= htmlspecialchars($home_data['second_button_link'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Section CTA -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Appel à l'action</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="cta_title" id="cta_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['cta_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="cta_text" id="cta_text" rows="2"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['cta_text'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="mt-8">
                        <button type="submit" 
                                class="w-full md:w-auto px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/>
                            </svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
</section>
            <!-- Liste des CV existants -->
<section class="mb-8">
    <div class="bg-white shadow-xl rounded-lg border border-purple-100 p-6">
        <?php
        $query = "SELECT * FROM cv ORDER BY date_ajout DESC";
        $result = $conn->query($query);
        ?>

        <h3 class="text-2xl font-bold text-purple-800 mb-6 border-l-4 border-purple-500 pl-4">
            CV existants (<?= $result->num_rows ?>)
        </h3>

        <div class="overflow-x-auto rounded-lg border border-purple-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Statut</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Date d'ajout</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($cv = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                <?= $cv['actif'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                <?= $cv['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($cv['nom_cv']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($cv['date_ajout'])) ?></td>
                        <td class="px-6 py-4 text-sm space-x-4">
                            <a href="download_cv.php?id=<?= $cv['id'] ?>" 
                               class="text-purple-600 hover:text-purple-900 transition-colors"
                               title="Télécharger">
                                <i class="fas fa-download text-lg"></i>
                            </a>
                            <a href="edit_cv.php?id=<?= $cv['id'] ?>" 
                               class="text-purple-600 hover:text-purple-900 transition-colors"
                               title="Modifier">
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <a href="delete_cv.php?id=<?= $cv['id'] ?>" 
                               class="text-red-600 hover:text-red-900 transition-colors"
                               title="Supprimer"
                               onclick="return confirm('Supprimer définitivement ce CV ?')">
                                <i class="fas fa-trash text-lg"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

    <script>
    function previewFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            input.parentNode.querySelector('.file-name').textContent = fileName;
        }
    }
    </script>
 <!-- section compétences -->
<div class="max-w-8xl mx-auto py-8 px-4">
    <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-purple-800">
                    <i class="fas fa-tools mr-2"></i>Gestion des Compétences
                </h1>
            </div>

            <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-100 p-4 mb-6 rounded-lg border border-green-200">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
            <?php endif; ?>

            <div class="rounded-lg shadow overflow-hidden border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Icône</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Titre</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Description</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-purple-800">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($skills as $skill): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <i class="fas fa-<?= htmlspecialchars($skill['icon']) ?> text-purple-600 text-lg"></i>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($skill['title']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($skill['description']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="edit_skill.php?id=<?= $skill['id'] ?>" 
                                   class="text-purple-600 hover:text-purple-800 mr-4 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_skill.php?delete=1&id=<?= $skill['id'] ?>" 
   class="text-red-600 hover:text-red-800 transition-colors"
   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette compétence ?')">
    <i class="fas fa-trash"></i>
</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Liste des médias sociaux -->
<section class="mb-8">
    <div class="bg-white shadow-xl rounded-lg border border-purple-100 p-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-6 border-l-4 border-purple-500 pl-4">
            Liste des médias sociaux
        </h2>

        <div class="overflow-x-auto rounded-lg border border-purple-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Lien</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($media = $result_social_media->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-900 font-medium"><?= htmlspecialchars($media['id']) ?></td>
                        <td class="px-6 py-4 text-gray-900"><?= htmlspecialchars($media['nom']) ?></td>
                        <td class="px-6 py-4 text-purple-600 hover:text-purple-900">
                            <a href="<?= htmlspecialchars($media['link']) ?>" target="_blank">
                                <?= htmlspecialchars($media['link']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 space-x-4">
                            <a href="edit_social_media.php?id=<?= $media['id'] ?>" 
                               class="text-purple-600 hover:text-purple-900 transition-colors">
                                Modifier
                            </a>
                            <a href="delete_social_media.php?id=<?= $media['id'] ?>" 
                               class="text-red-600 hover:text-red-900 transition-colors"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce média social ?');">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<!-- Liste des adhérents -->
<section class="mb-8">
    <div class="bg-white shadow-xl rounded-lg border border-purple-100 p-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-6 border-l-4 border-purple-500 pl-4">
            Liste des adhérents
        </h2>
        
        <div class="overflow-x-auto rounded-lg border border-purple-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Photo</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Prénom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-purple-800">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($adherent = $result_adherents->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="<?= $adherent['profile_photo'] ? htmlspecialchars($adherent['profile_photo']) : '../images/default-avatar.png' ?>" 
                                 alt="Photo de profil <?= htmlspecialchars($adherent['prenom']) ?>" 
                                 class="w-12 h-12 rounded-full object-cover border-2 border-purple-200">
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-medium"><?= htmlspecialchars($adherent['id']) ?></td>
                        <td class="px-6 py-4 text-gray-900"><?= htmlspecialchars($adherent['nom']) ?></td>
                        <td class="px-6 py-4 text-gray-900"><?= htmlspecialchars($adherent['prenom']) ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($adherent['email']) ?></td>
                        <td class="px-6 py-4 space-x-4">
                            <a href="edit_adherent.php?id=<?= $adherent['id'] ?>" 
                               class="text-purple-600 hover:text-purple-900 transition-colors">
                                Modifier
                            </a>
                            <a href="delete_adherent.php?id=<?= $adherent['id'] ?>" 
                               class="text-red-600 hover:text-red-900 transition-colors"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?');">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>


<!-- Liste des images -->
<section class="mb-8">
    <div class="bg-white shadow-xl rounded-lg border border-purple-100 p-6">
        <h2 class="text-2xl font-bold text-purple-800 mb-6 border-l-4 border-purple-500 pl-4">
            Liste des Images
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $query = "SELECT * FROM images ORDER BY upload_date DESC";
            $result_images = $conn->query($query);
            while ($image = $result_images->fetch_assoc()): ?>
                <div class="bg-white rounded-lg border border-purple-100 hover:shadow-lg transition-shadow">
                    <!-- Titre de l'image -->
                    <div class="p-4 border-b border-purple-100">
                        <h3 class="text-lg font-semibold text-gray-900 truncate"><?= htmlspecialchars($image['title']) ?></h3>
                    </div>

                    <!-- Image -->
                    <img src="/BUT2/S4/Portofolio-Back/lib/uploadPhoto/<?= htmlspecialchars($image['filename']) ?>"
                         alt="<?= htmlspecialchars($image['filename']) ?>"
                         class="w-full h-48 object-cover">

                    <!-- Détails et actions -->
                    <div class="p-4 space-y-2">
                        <p class="text-xs text-gray-500">Ajouté le <?= date('d/m/Y', strtotime($image['upload_date'])) ?></p>
                        <div class="flex space-x-3">
                            <a href="edit_image.php?id=<?= $image['id'] ?>"
                               class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i>Modifier
                            </a>
                            <a href="delete_image.php?id=<?= $image['id'] ?>"
                               class="text-red-600 hover:text-red-900 text-sm transition-colors"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?');">
                                <i class="fas fa-trash mr-1"></i>Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


   
    <script src="https://cdn.tiny.cloud/1/votre-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
</body>
</html>
<?php include 'footer.php'; ?>