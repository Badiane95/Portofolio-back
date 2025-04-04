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
$query_projects = "SELECT 
    id, 
    name, 
    description, 
    start_date, 
    end_date, 
    status, 
    image_path, 
    alt_text, 
    created_at 
    FROM projects 
    ORDER BY created_at DESC";
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
while($row = $result_home_content->fetch_assoc()){
    $home_data[$row['section_name']] = $row['content'];
}
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
            <h1 class="text-3xl font-bold text-purple-900 mb-4">Bienvenue dans le tableau de bord, <?php echo $_SESSION['admin']; ?> !</h1>
            
            <!-- Liste des projets -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-3">Liste des Projets</h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <?php if ($result_projects && $result_projects->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                            <?php while($project = $result_projects->fetch_assoc()): ?>
                                <div class="bg-gray-50 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                    <img src="<?= htmlspecialchars($project['image_path'] ?? '') ?>" 
                                         alt="<?= htmlspecialchars($project['alt_text'] ?? 'Image du projet') ?>" 
                                         class="w-full h-48 object-cover">
                                    
                                    <div class="p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg font-semibold text-gray-800">
                                                <?= htmlspecialchars($project['name'] ?? 'Nom non disponible') ?>
                                            </h3>
                                            <span class="px-3 py-1 text-sm rounded-full 
                                                <?= match($project['status'] ?? '') {
                                                    'planned' => 'bg-blue-100 text-blue-800',
                                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                    'completed' => 'bg-green-100 text-green-800',
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
                                        <p class="text-sm text-gray-700 mb-4">
                                            <?= htmlspecialchars($project['description']) ?>
                                        </p>
                                        <?php endif; ?>

                                        <div class="text-sm text-gray-600 mb-3">
                                            <?php if(!empty($project['start_date'])): ?>
                                            <p class="mb-1">
                                                <span class="font-medium">Début:</span> 
                                                <?= date('d/m/Y', strtotime($project['start_date'])) ?>
                                            </p>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($project['end_date'])): ?>
                                            <p>
                                                <span class="font-medium">Fin:</span> 
                                                <?= date('d/m/Y', strtotime($project['end_date'])) ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex justify-end space-x-2">
                                            <a href="edit_project.php?id=<?= (int)($project['id'] ?? 0) ?>" 
                                               class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Modifier
                                            </a>
                                            <span class="text-gray-400">|</span>
                                            <a href="delete_project.php?id=<?= (int)($project['id'] ?? 0) ?>" 
                                               class="text-red-600 hover:text-red-900 text-sm"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                                                Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-gray-500">Aucun projet trouvé</div>
                    <?php endif; ?>
                </div>
            </section>
    
<section>
  <!-- Liste des vidéos -->
<div class="space-y-6">
<h2 class="text-4xl font-bold text-purple-800 mb-4">
                Mes Projet vidéos
            </h2>
    <?php
    $videos = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
    while($video = $videos->fetch_assoc()):
    ?>
    <div class="group bg-white hover:bg-purple-50 rounded-xl shadow-md hover:shadow-lg p-6 border-2 border-purple-50 transition-all">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-4">
            <h3 class="text-lg font-bold text-purple-900 flex-1 truncate">
                <?= htmlspecialchars($video['title']) ?>
            </h3>
            
            <div class="flex gap-2">
                <a href="edit_video.php?id=<?= $video['id'] ?>" 
                   class="px-4 py-2 bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 rounded-lg border border-purple-200 transition-all">
                   ✏️ Modifier
                </a>
                <a href="delete_video.php?id=<?= $video['id'] ?>" 
                   class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-700 rounded-lg border border-red-200 transition-all"
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')">
                   🗑️ Supprimer
                </a>
            </div>
        </div>
        
        <?php if (!empty($video['description'])): ?>
        <p class="text-purple-600 mb-4 text-sm leading-relaxed">
            <?= nl2br(htmlspecialchars($video['description'])) ?>
        </p>
        <?php endif; ?>
        
        <div class="aspect-video rounded-xl overflow-hidden border-2 border-purple-100 bg-gray-50">
            <iframe src="<?= htmlspecialchars($video['video_url']) ?>" 
                class="w-full h-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</section>



<!-- Section Gestion des Champs du Formulaire -->
<section class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-800">Gestion des Champs du Formulaire</h2>
        <a href="add_field.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Ajouter un champ
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
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
                                           class="w-16 px-2 py-1 border rounded" min="1">
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

                        <!-- Première Section -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Première Section</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="first_title" id="first_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_title'] ?? '') ?>">
                            </div>

                            <!-- Item 1 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 1</label>
                                <input type="text" name="first_item1_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item1_title'] ?? '') ?>">
                                <input type="text" name="first_item1_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item1_text'] ?? '') ?>">
                            </div>

                            <!-- Item 2 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 2</label>
                                <input type="text" name="first_item2_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item2_title'] ?? '') ?>">
                                <input type="text" name="first_item2_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item2_text'] ?? '') ?>">
                            </div>

                            <!-- Item 3 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 3</label>
                                <input type="text" name="first_item3_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item3_title'] ?? '') ?>">
                                <input type="text" name="first_item3_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item3_text'] ?? '') ?>">
                            </div>
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
            <?php
            $query = "SELECT * FROM cv ORDER BY date_ajout DESC";
            $result = $conn->query($query);
            ?>

            <div class="px-6 pb-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-3">CV existants (<?= $result->num_rows ?>)</h3>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Nom</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Date d'ajout</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($cv = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $cv['actif'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                        <?= $cv['actif'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($cv['nom_cv']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($cv['date_ajout'])) ?></td>
                                <td class="px-4 py-3 text-sm space-x-2">
                                    <a href="download_cv.php?id=<?= $cv['id'] ?>" class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Supprimer définitivement ce CV ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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


            <!-- Liste des adhérents -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-3">Liste des adhérents</h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($adherent = $result_adherents->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img src="<?= $adherent['profile_photo'] ? htmlspecialchars($adherent['profile_photo']) : '../images/default-avatar.png' ?>" 
                                         alt="Photo de profil <?= htmlspecialchars($adherent['prenom']) ?>" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-purple-100">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($adherent['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($adherent['nom']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($adherent['prenom']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($adherent['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_adherent.php?id=<?= $adherent['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</a>
                                    <a href="delete_adherent.php?id=<?= $adherent['id'] ?>" 
                                       class="text-red-600 hover:text-red-900" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet adhérent ?');">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Liste des médias sociaux -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-3">Liste des médias sociaux</h2>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lien</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($media = $result_social_media->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($media['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($media['nom']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($media['link']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_social_media.php?id=<?php echo $media['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Modifier</a>
                                    <a href="delete_social_media.php?id=<?php echo $media['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce média social ?');">Supprimer</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>


            <!-- Liste des images -->
<section>
    <h2 class="text-2xl font-semibold text-gray-800 mb-3">Liste des Images</h2>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-4">
            <?php
            $query = "SELECT * FROM images ORDER BY upload_date DESC";
            $result_images = $conn->query($query);
            while ($image = $result_images->fetch_assoc()): ?>
                <div class="bg-gray-100 rounded-lg overflow-hidden shadow-md">
                    <img src="/BUT2/S4/Portofolio-Back/lib/uploadPhoto/<?php echo htmlspecialchars($image['filename']); ?>" 
                         alt="<?php echo htmlspecialchars($image['filename']); ?>" 
                         class="w-full h-48 object-cover">
                    <div class="p-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2"><?php echo htmlspecialchars($image['filename']); ?></p>
                        <p class="text-xs text-gray-500 mb-2">Ajouter: <?php echo date('d/m/Y', strtotime($image['upload_date'])); ?></p>
                        <a href="delete_image.php?id=<?php echo $image['id']; ?>" 
                           class="text-red-600 hover:text-red-900 text-xs"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?');">
                            Supprimer
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


   
    <script src="https://cdn.tiny.cloud/1/votre-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
</body>
<?php include 'footer.php'; ?>
</html>
