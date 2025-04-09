<?php

// Gestionnaire de messages
if (isset($_SESSION['message'])) {
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
            <span class='block sm:inline'>" . $_SESSION['message'] . "</span>
          </div>";
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
            <span class='block sm:inline'>" . $_SESSION['error'] . "</span>
          </div>";
    unset($_SESSION['error']);
}

// Initialisation des variables
$search_term = '';
$search_category = 'all';
$results = [];
$categories = [
    'all' => 'Toutes les catégories',
    'projects' => 'Projets',
    'adherents' => 'Adhérents',
    'images' => 'Images',
    'skills' => 'Compétences',
    'videos' => 'Vidéos',
    'cv' => 'CV',
    'form_fields' => 'Champs Formulaire',
    'social_media' => 'Médias Sociaux'
];

// Traitement des opérations CRUD
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
    
    // Delete - Suppression d'un élément
    if ($action === 'delete' && $id) {
        $category = $_GET['category'];
        handleDelete($conn, $category, $id);
    }
    
    // Edit - Formulaire de modification
    if ($action === 'edit' && $id) {
        $category = $_GET['category'];
        $item = getItemById($conn, $category, $id);
    }
    
    // Update - Mise à jour d'un élément
    if ($action === 'update' && isset($_POST['submit'])) {
        $category = $_POST['category'];
        handleUpdate($conn, $category, $_POST);
    }
    
    // Create - Création d'un nouvel élément
    if ($action === 'create' && isset($_POST['submit'])) {
        $category = $_POST['category'];
        handleCreate($conn, $category, $_POST);
    }
}

// Traitement de la recherche
if (isset($_GET['search']) && isset($_GET['category'])) {
    $search_term = htmlspecialchars(trim($_GET['search']));
    $search_category = htmlspecialchars($_GET['category']);
    
    if (!empty($search_term)) {
        $results = performSearch($conn, $search_term, $search_category);
    }
}

/**
 * Fonction pour effectuer une recherche dans la base de données
 */
function performSearch($conn, $search_term, $category) {
    $results = [];
    $search_term = "%{$search_term}%";
    
    // Recherche dans toutes les catégories ou une catégorie spécifique
    if ($category === 'all' || $category === 'projects') {
        $stmt = $conn->prepare("SELECT id, name, description, status, created_at FROM projects WHERE name LIKE ? OR description LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'projects';
            $results[] = $row;
        }
    }    // Vidéos
    if ($category === 'all' || $category === 'videos') {
        $stmt = $conn->prepare("SELECT id, title, description, video_url, created_at FROM videos 
                              WHERE title LIKE ? OR description LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'videos';
            $results[] = $row;
        }
    }

    // CV
    if ($category === 'all' || $category === 'cv') {
        $stmt = $conn->prepare("SELECT id, nom_cv, date_ajout, actif FROM cv 
                              WHERE nom_cv LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'cv';
            $results[] = $row;
        }
    }

    // Champs Formulaire
    if ($category === 'all' || $category === 'form_fields') {
        $stmt = $conn->prepare("SELECT id, field_name, label, field_type FROM contact_form 
                              WHERE field_name LIKE ? OR label LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'form_fields';
            $results[] = $row;
        }
    }

    // Médias Sociaux
    if ($category === 'all' || $category === 'social_media') {
        $stmt = $conn->prepare("SELECT id, nom, link FROM social_media 
                              WHERE nom LIKE ? OR link LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'social_media';
            $results[] = $row;
        }
    }

    
    if ($category === 'all' || $category === 'adherents') {
        $stmt = $conn->prepare("SELECT id, nom, prenom, email FROM adherents WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?");
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'adherents';
            $results[] = $row;
        }
    }
    
    if ($category === 'all' || $category === 'images') {
        $stmt = $conn->prepare("SELECT id, filename, title, upload_date FROM images WHERE filename LIKE ? OR title LIKE ?");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'images';
            $results[] = $row;
        }
    }
    
    if ($category === 'all' || $category === 'skills') {
        $stmt = $conn->prepare("SELECT id, name, level, created_at FROM skills WHERE name LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['category'] = 'skills';
            $results[] = $row;
        }
    }
    
    return $results;
}

/**
 * Fonction pour supprimer un élément
 */
function handleDelete($conn, $category, $id) {
    $table = '';
    
    switch ($category) {
        case 'projects':
            $table = 'projects';
            break;
        case 'adherents':
            $table = 'adherents';
            break;
        case 'images':
            $table = 'images';
            break;
        case 'skills':
            $table = 'skills';
            break;case 'videos':
                $table = 'videos';
                break;
            case 'cv':
                $table = 'cv';
                break;
            case 'form_fields':
                $table = 'contact_form';
                break;
            case 'social_media':
                $table = 'social_media';
                break;
            default:
                $_SESSION['error'] = "Catégorie invalide!";
                header("Location: search.php");
                exit;
               
    
    }
    
    $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Élément supprimé avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $conn->error;
    }
    
    header("Location: search.php");
    exit;
}

/**
 * Fonction pour récupérer un élément par son ID
 */
function getItemById($conn, $category, $id) {
    $table = '';
    $columns = '*';
    
    switch ($category) {
        case 'projects':
            $table = 'projects';
            break;
        case 'adherents':
            $table = 'adherents';
            break;
        case 'images':
            $table = 'images';
            break;
        case 'skills':
            $table = 'skills';
            break;
        case 'videos':
                $table = 'videos';
                break;
        case 'cv':
                $table = 'cv';
                break;
        case 'form_fields':
                $table = 'contact_form';
                break;
        case 'social_media':
                $table = 'social_media';
                break;
            
        default:
            return null;
    }
    
    $stmt = $conn->prepare("SELECT {$columns} FROM {$table} WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Fonction pour mettre à jour un élément
 */
function handleUpdate($conn, $category, $data) {
    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    
     {
        $conn->begin_transaction();
        $stmt = null;

        switch ($category) {
            case 'projects':
                // Récupération et validation des données
                $name = htmlspecialchars($data['name']);
                $description = htmlspecialchars($data['description']);
                $start_date = $data['start_date'];
                $end_date = $data['end_date'];
                $status = in_array($data['status'], ['planned', 'in_progress', 'completed']) ? $data['status'] : 'planned';
                $alt_text = htmlspecialchars($data['alt_text']);
                $project_link = htmlspecialchars($data['project_link']);
                $existing_image = $data['existing_image'] ?? '';

                // Configuration upload
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadArticle/';
                $maxFileSize = 5 * 1024 * 1024;
                $allowedTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
                $relativePath = $existing_image;
                $oldImage = '';

                // Gestion de la nouvelle image
                if (!empty($_FILES['image']['tmp_name'])) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($_FILES['image']['tmp_name']);
                    $extension = array_search($mimeType, $allowedTypes, true);

                    if ($_FILES['image']['size'] > $maxFileSize) {
                        throw new Exception("Fichier trop volumineux (>5Mo)");
                    }
                    if (!$extension) {
                        throw new Exception("Type de fichier non autorisé");
                    }

                    $newFileName = 'project-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $filePath = $uploadDir . $newFileName;

                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                        throw new Exception("Échec de l'upload");
                    }

                    // Récupération ancienne image
                    $stmt = $conn->prepare("SELECT image_path FROM projects WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $oldImage = $stmt->get_result()->fetch_assoc()['image_path'];
                    $stmt->close();

                    $relativePath = '/BUT2/S4/Portofolio-Back/lib/uploadArticle/' . $newFileName;
                }

                // Mise à jour BDD
                $stmt = $conn->prepare("UPDATE projects SET 
                    name=?, description=?, start_date=?, end_date=?, status=?, 
                    image_path=?, alt_text=?, project_link=?, updated_at=NOW() 
                    WHERE id=?");

                $stmt->bind_param("ssssssssi", 
                    $name, $description, $start_date, $end_date, $status,
                    $relativePath, $alt_text, $project_link, $id
                );
                break;

            case 'adherents':
                $nom = htmlspecialchars($data['nom']);
                $prenom = htmlspecialchars($data['prenom']);
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email invalide");
                }
                
                $stmt = $conn->prepare("UPDATE adherents SET nom = ?, prenom = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nom, $prenom, $email, $id);
                break;

            case 'images':
                $title = htmlspecialchars($data['title']);
                $stmt = $conn->prepare("UPDATE images SET title = ? WHERE id = ?");
                $stmt->bind_param("si", $title, $id);
                break;

            case 'skills':
                $name = htmlspecialchars($data['name']);
                $level = filter_var($data['level'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 100]
                ]);
                
                if ($level === false) {
                    throw new Exception("Niveau invalide (1-100)");
                }
                
                $stmt = $conn->prepare("UPDATE skills SET name = ?, level = ? WHERE id = ?");
                $stmt->bind_param("sii", $name, $level, $id);
                break;

            case 'videos':
                $title = htmlspecialchars($data['title']);
                $description = htmlspecialchars($data['description']);
                $video_url = filter_var($data['video_url'], FILTER_VALIDATE_URL);
                
                if (!$video_url) {
                    throw new Exception("URL vidéo invalide");
                }
                
                $stmt = $conn->prepare("UPDATE videos SET title = ?, description = ?, video_url = ? WHERE id = ?");
                $stmt->bind_param("sssi", $title, $description, $video_url, $id);
                break;

            case 'cv':
                $nom_cv = htmlspecialchars($data['nom_cv']);
                // Ajouter la logique de mise à jour des CV si nécessaire
                throw new Exception("Fonctionnalité CV non implémentée");
                break;

            case 'form_fields':
                $field_name = htmlspecialchars($data['field_name']);
                $label = htmlspecialchars($data['label']);
                $field_type = htmlspecialchars($data['field_type']);
                
                $allowedTypes = ['text', 'email', 'textarea'];
                if (!in_array($field_type, $allowedTypes)) {
                    throw new Exception("Type de champ invalide");
                }
                
                $stmt = $conn->prepare("UPDATE contact_form SET field_name = ?, label = ?, field_type = ? WHERE id = ?");
                $stmt->bind_param("sssi", $field_name, $label, $field_type, $id);
                break;

            case 'social_media':
                $nom = htmlspecialchars($data['nom']);
                $link = filter_var($data['link'], FILTER_VALIDATE_URL);
                
                if (!$link) {
                    throw new Exception("URL invalide");
                }
                
                $stmt = $conn->prepare("UPDATE social_media SET nom = ?, link = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nom, $link, $id);
                break;

            default:
                throw new Exception("Catégorie invalide");
        }

        if (!$stmt || !$stmt->execute()) {
            throw new Exception($stmt ? $stmt->error : "Requête non préparée");
        }

        $conn->commit();
        $_SESSION['message'] = "Mise à jour réussie !";

        // Nettoyage ancienne image (uniquement pour les projets)
        if ($category === 'projects' && !empty($oldImage) && $oldImage !== $relativePath) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $oldImage;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

    
    }

    header("Location: dashboard.php");
    exit();
}

/**
 * Fonction pour créer un nouvel élément
 */

function handleCreate($conn, $category, $data) {
    // Démarrer une transaction pour l'intégrité des données
    $conn->begin_transaction();

     {
        switch ($category) {
            case 'projects':
                $name = htmlspecialchars($data['name']);
                $description = htmlspecialchars($data['description']);
                $status = htmlspecialchars($data['status']);
                
                $stmt = $conn->prepare("INSERT INTO projects (name, description, status) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $description, $status);
                break;

            case 'adherents':
                $nom = htmlspecialchars($data['nom']);
                $prenom = htmlspecialchars($data['prenom']);
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email invalide");
                }
                
                $stmt = $conn->prepare("INSERT INTO adherents (nom, prenom, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nom, $prenom, $email);
                break;

            case 'images':
                $title = htmlspecialchars($data['title']);
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/';
                
                // Validation du fichier
                $fileInfo = getimagesize($_FILES['image']['tmp_name']);
                if (!$fileInfo) {
                    throw new Exception("Fichier image invalide");
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $filepath = $uploadDir . $filename;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                    throw new Exception("Échec de l'upload de l'image");
                }

                $stmt = $conn->prepare("INSERT INTO images (filename, filepath, title) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $filename, $filepath, $title);
                break;

            case 'skills':
                $name = htmlspecialchars($data['name']);
                $level = filter_var($data['level'], FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 100]
                ]);
                
                if ($level === false) {
                    throw new Exception("Niveau de compétence invalide");
                }

                $stmt = $conn->prepare("INSERT INTO skills (name, level) VALUES (?, ?)");
                $stmt->bind_param("si", $name, $level);
                break;

            case 'videos':
                $title = htmlspecialchars($data['title']);
                $description = htmlspecialchars($data['description']);
                $video_url = filter_var($data['video_url'], FILTER_VALIDATE_URL);
                
                if (!$video_url || !preg_match('/youtube|vimeo/i', $video_url)) {
                    throw new Exception("URL vidéo non valide");
                }

                $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $title, $description, $video_url);
                break;

            case 'cv':
                $nom_cv = htmlspecialchars($data['nom_cv']);
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/BUT2/S4/Portofolio-Back/lib/uploadCV/';
                $allowedTypes = ['application/pdf'];
                
                // Validation PDF
                $fileType = $_FILES['cv_file']['type'];
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Seuls les fichiers PDF sont autorisés");
                }
                
                $filename = uniqid() . '_' . basename($_FILES['cv_file']['name']);
                $filepath = $uploadDir . $filename;

                if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $filepath)) {
                    throw new Exception("Échec de l'upload du CV");
                }

                $stmt = $conn->prepare("INSERT INTO cv (nom_cv, fichier, chemin) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nom_cv, $filename, $filepath);
                break;

            case 'form_fields':
                $field_name = htmlspecialchars($data['field_name']);
                $label = htmlspecialchars($data['label']);
                $field_type = htmlspecialchars($data['field_type']);
                
                $allowedTypes = ['text', 'email', 'textarea'];
                if (!in_array($field_type, $allowedTypes)) {
                    throw new Exception("Type de champ non valide");
                }

                $stmt = $conn->prepare("INSERT INTO contact_form (field_name, label, field_type) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $field_name, $label, $field_type);
                break;

            case 'social_media':
                $nom = htmlspecialchars($data['nom']);
                $link = filter_var($data['link'], FILTER_VALIDATE_URL);
                
                if (!$link) {
                    throw new Exception("URL de réseau social invalide");
                }

                $stmt = $conn->prepare("INSERT INTO social_media (nom, link) VALUES (?, ?)");
                $stmt->bind_param("ss", $nom, $link);
                break;

            default:
                throw new Exception("Catégorie invalide");
        }

        // Exécution de la requête
        if (!$stmt->execute()) {
            throw new Exception("Erreur d'exécution de la requête");
        }

        // Validation de la transaction
        $conn->commit();
        $_SESSION['message'] = "Élément créé avec succès!";

  
    }

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Recherche avancée</h1>
        
      <!-- Formulaire de recherche -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[250px]">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
            <input 
                type="text" 
                id="search" 
                name="search" 
                value="<?= htmlspecialchars($search_term) ?>" 
                placeholder="Entrez votre recherche..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            >
        </div>
        
        <div class="flex-1 min-w-[250px]">
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
            <select 
                id="category" 
                name="category" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            >
                <?php foreach ($categories as $key => $value): ?>
                    <option 
                        value="<?= htmlspecialchars($key) ?>" 
                        <?= $search_category === $key ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex-1 min-w-[150px]">
            <button 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md flex items-center justify-center gap-2 transition-colors"
            >
                <i class="fas fa-search text-sm"></i>
                <span>Rechercher</span>
            </button>
        </div>
    </form>
</div>
        

        
       <!-- Résultats de recherche -->
<?php if (!empty($search_term)): ?>
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">
            Résultats pour "<?= htmlspecialchars($search_term) ?>"
            <?php if ($search_category !== 'all'): ?>
            dans <?= $categories[$search_category] ?>
            <?php endif; ?>
        </h2>
    </div>
    
    <?php if (empty($results)): ?>
    <div class="p-6 text-center text-gray-700">
        Aucun résultat trouvé.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom/Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($results as $item): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $item['id'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?php switch($item['category']):
                                case 'projects': ?>
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?php break; ?>
                                
                                case 'adherents': ?>
                                    <?= htmlspecialchars($item['nom']) ?> <?= htmlspecialchars($item['prenom']) ?>
                                    <?php break; ?>
                                
                                case 'images': ?>
                                    <?= htmlspecialchars($item['title']) ?>
                                    <?php break; ?>
                                
                                case 'skills': ?>
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?php break; ?>
                                
                                case 'videos': ?>
                                    <?= htmlspecialchars($item['title']) ?>
                                    <?php break; ?>
                                
                                case 'cv': ?>
                                    <?= htmlspecialchars($item['nom_cv']) ?>
                                    <?php break; ?>
                                
                                case 'form_fields': ?>
                                    <?= htmlspecialchars($item['field_name']) ?>
                                    <?php break; ?>
                                
                                case 'social_media': ?>
                                    <?= htmlspecialchars($item['nom']) ?>
                                    <?php break; ?>
                                
                                default: ?>
                                    N/A
                            <?php endswitch; ?>
                        </div>
                        <?php if (!empty($item['description']) || !empty($item['link'])): ?>
                        <div class="text-sm text-gray-500 truncate max-w-xs">
                            <?= htmlspecialchars(substr($item['description'] ?? $item['link'] ?? '', 0, 100)) ?>
                            <?= strlen($item['description'] ?? $item['link'] ?? '') > 100 ? '...' : '' ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= match($item['category']) {
                                'projects' => 'bg-blue-100 text-blue-800',
                                'adherents' => 'bg-green-100 text-green-800',
                                'images' => 'bg-purple-100 text-purple-800',
                                'skills' => 'bg-yellow-100 text-yellow-800',
                                'videos' => 'bg-pink-100 text-pink-800',
                                'cv' => 'bg-orange-100 text-orange-800',
                                'form_fields' => 'bg-cyan-100 text-cyan-800',
                                'social_media' => 'bg-fuchsia-100 text-fuchsia-800',
                                default => 'bg-gray-100 text-gray-800'
                            } ?>">
                            <?= $categories[$item['category']] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php 
                        $dateFields = [
                            'created_at', 
                            'upload_date', 
                            'date_ajout', 
                            'created_date'
                        ];
                        
                        $date = 'N/A';
                        foreach ($dateFields as $field) {
                            if (!empty($item[$field])) {
                                $date = date('d/m/Y H:i', strtotime($item[$field]));
                                break;
                            }
                        }
                        echo $date;
                        ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="?action=edit&category=<?= $item['category'] ?>&id=<?= $item['id'] ?>" 
                           class="text-indigo-600 hover:text-indigo-900">
                           <i class="fas fa-edit mr-1"></i>Modifier
                        </a>
                        <a href="?action=delete&category=<?= $item['category'] ?>&id=<?= $item['id'] ?>" 
                           class="text-red-600 hover:text-red-900"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');">
                           <i class="fas fa-trash-alt mr-1"></i>Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
        
        <!-- Modal d'édition (caché par défaut) -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Modifier l'élément</h3>
            <button id="closeEditModal" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST" action="?action=update" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="category" id="editCategory">
           
               
            <!-- Projet Fields -->
    <div id="projectFields" class="hidden max-w-3xl mx-auto px-4 py-8">
     <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-project-diagram mr-2"></i>Modifier le projet
                </h2>

                <!-- Affichage des messages -->
                <?php if(isset($_SESSION['success'])): ?>
                <!-- Message de succès -->
                <div class="p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); // Suppression du message de la session ?>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                <!-- Message d'erreur -->
                <div class="p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); // Suppression du message de la session ?>
                </div>
                <?php endif; ?>

                <!-- Formulaire de modification du projet -->
                <form action="edit_project.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Token CSRF pour la sécurité -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <!-- ID du projet -->
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <!-- Champs du formulaire -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom du projet -->
                        <div>
                            <!-- Label pour le nom -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <!-- Input pour le nom -->
                            <input type="text" name="name" value="<?= htmlspecialchars_decode($project['name'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" required>
                        </div>

                        <!-- Statut du projet -->
                        <div>
                            <!-- Label pour le statut -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <!-- Select pour le statut -->
                            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="planned" <?= ($project['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planifié</option>
                                <option value="in_progress" <?= ($project['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description du projet -->
                    <div>
                        <!-- Label pour la description -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <!-- Textarea pour la description -->
                        <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars_decode($project['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>

                    <!-- Dates du projet -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date de début -->
                        <div>
                            <!-- Label pour la date de début -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <!-- Input pour la date de début -->
                            <input type="date" name="start_date" value="<?= htmlspecialchars_decode($project['start_date'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <!-- Date de fin -->
                        <div>
                            <!-- Label pour la date de fin -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <!-- Input pour la date de fin -->
                            <input type="date" name="end_date" value="<?= htmlspecialchars_decode($project['end_date'] ?? '', ENT_QUOTES) ?>" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <!-- Zone de dépôt d'image -->
                    <div>
                        <!-- Label pour l'image -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                        <!-- Zone de dépôt -->
                        <div id="drop-zone" class="border-2 border-dashed border-purple-200 rounded-lg p-6 cursor-pointer hover:border-purple-400">
                            <div class="text-center">
                                <!-- Affichage de l'image existante ou indication de dépôt -->
                                <?php if(!empty($project['image_path'])): ?>
                                    <!-- Image existante -->
                                    <img id="preview" src="<?= htmlspecialchars($project['image_path']) ?>" 
                                         alt="Preview" class="mt-4 mx-auto max-h-40 rounded-lg">
                                <?php else: ?>
                                    <!-- Indication de dépôt -->
                                    <i class="fas fa-cloud-upload-alt text-4xl text-purple-400 mb-4"></i>
                                    <p class="font-medium text-gray-600">Glissez-déposez ou cliquez pour uploader</p>
                                    <img id="preview" src="" alt="Preview" class="mt-4 mx-auto max-h-40 rounded-lg hidden">
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Input de type fichier caché -->
                        <input type="file" name="image" id="image" class="hidden">
                        <!-- Input caché pour le chemin de l'image existante -->
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($project['image_path'] ?? '') ?>">
                    </div>

                    <!-- Autres champs -->
                    <div>
                        <!-- Label pour le lien du projet -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lien du projet</label>
                        <!-- Input pour le lien du projet -->
                        <input type="url" name="project_link" 
                               value="<?= htmlspecialchars_decode($project['project_link'] ?? '', ENT_QUOTES) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="https://example.com">
                    </div>

                    <div>
                        <!-- Label pour la description alternative -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description alternative</label>
                        <!-- Input pour la description alternative -->
                        <input type="text" name="alt_text" 
                               value="<?= htmlspecialchars_decode($project['alt_text'] ?? '', ENT_QUOTES) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500" 
                               placeholder="Description pour l'accessibilité">
                    </div>

                    <!-- Section pour les boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <!-- Lien pour retourner au tableau de bord -->
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Retour</a>
                            <!-- Bouton pour enregistrer les modifications -->
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


            <!-- Adhérents -->
            <div id="adherentFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="adherentNom" name="nom" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="adherentPrenom" name="prenom" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="adherentEmail" name="email" class="w-full px-4 py-2 border rounded-md">
                </div>
            </div>

            <!-- Images -->
            <div id="imageFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" id="imageTitle" name="title" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouvelle image (optionnel)</label>
                    <input type="file" name="image" class="w-full px-4 py-2 border rounded-md">
                </div>
            </div>

            <!-- Compétences -->
            <div id="skillFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la compétence</label>
                    <input type="text" id="skillName" name="name" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Niveau (1-100)</label>
                    <input type="range" id="skillLevel" name="level" min="1" max="100" 
                           class="w-full" oninput="document.getElementById('skillLevelValue').textContent = this.value">
                    <span id="skillLevelValue">50</span>%
                </div>
            </div>

            <!-- Vidéos -->
            <div id="videoFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" id="videoTitle" name="title" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="url" id="videoUrl" name="video_url" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="videoDescription" name="description" class="w-full px-4 py-2 border rounded-md" rows="3"></textarea>
                </div>
            </div>

            <!-- CV -->
            <div id="cvFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV</label>
                    <input type="text" id="cvName" name="nom_cv" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau fichier (optionnel)</label>
                    <input type="file" name="cv_file" accept=".pdf" class="w-full px-4 py-2 border rounded-md">
                </div>
            </div>

            <!-- Champs Formulaire -->
            <div id="formFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du champ</label>
                    <input type="text" id="fieldName" name="field_name" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                    <input type="text" id="fieldLabel" name="label" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="fieldType" name="field_type" class="w-full px-4 py-2 border rounded-md">
                        <option value="text">Texte</option>
                        <option value="email">Email</option>
                        <option value="textarea">Zone de texte</option>
                    </select>
                </div>
            </div>

            <!-- Réseaux Sociaux -->
            <div id="socialMediaFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="socialName" name="nom" class="w-full px-4 py-2 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lien</label>
                    <input type="url" id="socialLink" name="link" class="w-full px-4 py-2 border rounded-md">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" id="cancelEdit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Annuler
                </button>
                <button type="submit" name="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
        
     <!-- Modal d'ajout (caché par défaut) -->
<div id="newItemModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Ajouter un nouvel élément</h3>
            <button id="closeNewItemModal" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mb-4">
            <label for="newItemCategory" class="block text-sm font-medium text-gray-700 mb-1">Type d'élément</label>
            <select id="newItemCategory" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <option value="projects">Projet</option>
                <option value="adherents">Adhérent</option>
                <option value="images">Image</option>
                <option value="skills">Compétence</option>
                <option value="videos">Vidéo</option>
                <option value="cv">CV</option>
                <option value="form_fields">Champ Formulaire</option>
                <option value="social_media">Réseau Social</option>
            </select>
        </div>

        <form id="newItemForm" method="POST" action="?action=create" enctype="multipart/form-data">
            <input type="hidden" name="category" id="newItemCategoryInput">

            <!-- Projets -->
            <div id="newProjectFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du projet</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" class="w-full px-4 py-2 border rounded-md" rows="3" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-md" required>
                        <option value="en cours">En cours</option>
                        <option value="terminé">Terminé</option>
                    </select>
                </div>
            </div>

            <!-- Adhérents -->
            <div id="newAdherentFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="nom" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" name="prenom" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-4 py-2 border rounded-md" required>
                </div>
            </div>

            <!-- Images -->
            <div id="newImageFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" name="title" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fichier image</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-md" required>
                </div>
            </div>

            <!-- Compétences -->
            <div id="newSkillFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Niveau (1-100)</label>
                    <input type="range" name="level" min="1" max="100" 
                           class="w-full" oninput="document.getElementById('skillLevelValue').textContent = this.value" required>
                    <span id="skillLevelValue">50</span>%
                </div>
            </div>

            <!-- Vidéos -->
            <div id="newVideoFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" name="title" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL vidéo</label>
                    <input type="url" name="video_url" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" class="w-full px-4 py-2 border rounded-md" rows="2"></textarea>
                </div>
            </div>

            <!-- CV -->
            <div id="newCvFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV</label>
                    <input type="text" name="nom_cv" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fichier PDF</label>
                    <input type="file" name="cv_file" accept=".pdf" class="w-full px-4 py-2 border rounded-md" required>
                </div>
            </div>

            <!-- Champs Formulaire -->
            <div id="newFormFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom technique</label>
                    <input type="text" name="field_name" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                    <input type="text" name="label" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="field_type" class="w-full px-4 py-2 border rounded-md" required>
                        <option value="text">Texte</option>
                        <option value="email">Email</option>
                        <option value="textarea">Zone de texte</option>
                    </select>
                </div>
            </div>

            <!-- Réseaux Sociaux -->
            <div id="newSocialMediaFields" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du réseau</label>
                    <input type="text" name="nom" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lien</label>
                    <input type="url" name="link" class="w-full px-4 py-2 border rounded-md" required>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" id="cancelNewItem" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Annuler
                </button>
                <button type="submit" name="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>
    
<script>
    // Gestion des modals
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments du DOM pour le modal d'édition
        const editModal = document.getElementById('editModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEdit = document.getElementById('cancelEdit');
        const editLinks = document.querySelectorAll('a[href^="?action=edit"]');
        
        // Affichage du modal d'édition
        editLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const category = url.searchParams.get('category');
                const id = url.searchParams.get('id');
                
                fetch(`get_item.php?category=${category}&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // Reset et configuration de base
                        document.getElementById('editId').value = id;
                        document.getElementById('editCategory').value = category;
                        document.querySelectorAll('[id$="Fields"]').forEach(el => el.classList.add('hidden'));

                        // Remplissage des champs selon la catégorie
                        switch(category) {
                            case 'projects':
                                document.getElementById('projectFields').classList.remove('hidden');
                                document.getElementById('projectName').value = data.name;
                                document.getElementById('projectDescription').value = data.description;
                                document.getElementById('projectStatus').value = data.status;
                                break;
                            
                            case 'adherents':
                                document.getElementById('adherentFields').classList.remove('hidden');
                                document.getElementById('adherentNom').value = data.nom;
                                document.getElementById('adherentPrenom').value = data.prenom;
                                document.getElementById('adherentEmail').value = data.email;
                                break;
                            
                            case 'images':
                                document.getElementById('imageFields').classList.remove('hidden');
                                document.getElementById('imageTitle').value = data.title;
                                break;
                            
                            case 'skills':
                                document.getElementById('skillFields').classList.remove('hidden');
                                document.getElementById('skillName').value = data.name;
                                document.getElementById('skillLevel').value = data.level;
                                document.getElementById('skillLevelValue').textContent = data.level;
                                break;
                            
                            case 'videos':
                                document.getElementById('videoFields').classList.remove('hidden');
                                document.getElementById('videoTitle').value = data.title;
                                document.getElementById('videoUrl').value = data.video_url;
                                document.getElementById('videoDescription').value = data.description;
                                break;
                            
                            case 'cv':
                                document.getElementById('cvFields').classList.remove('hidden');
                                document.getElementById('cvName').value = data.nom_cv;
                                break;
                            
                            case 'form_fields':
                                document.getElementById('formFields').classList.remove('hidden');
                                document.getElementById('fieldName').value = data.field_name;
                                document.getElementById('fieldLabel').value = data.label;
                                document.getElementById('fieldType').value = data.field_type;
                                break;
                            
                            case 'social_media':
                                document.getElementById('socialMediaFields').classList.remove('hidden');
                                document.getElementById('socialName').value = data.nom;
                                document.getElementById('socialLink').value = data.link;
                                break;
                        }
                        
                        editModal.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la récupération des données');
                    });
            });
        });

        // Fermeture modal d'édition
        [closeEditModal, cancelEdit].forEach(element => {
            element.addEventListener('click', () => editModal.classList.add('hidden'));
        });

        // Gestion modal d'ajout
        const newItemBtn = document.getElementById('newItemBtn');
        const newItemModal = document.getElementById('newItemModal');
        const closeNewItemModal = document.getElementById('closeNewItemModal');
        const cancelNewItem = document.getElementById('cancelNewItem');
        const newItemCategory = document.getElementById('newItemCategory');
        const newItemCategoryInput = document.getElementById('newItemCategoryInput');
        
        // Mapping des catégories vers les IDs des champs
        const categoryFieldsMap = {
            'projects': 'newProjectFields',
            'adherents': 'newAdherentFields',
            'images': 'newImageFields',
            'skills': 'newSkillFields',
            'videos': 'newVideoFields',
            'cv': 'newCvFields',
            'form_fields': 'newFormFields',
            'social_media': 'newSocialMediaFields'
        };

        // Affichage modal d'ajout
        newItemBtn.addEventListener('click', () => {
            newItemModal.classList.remove('hidden');
            updateNewItemFields();
        });

        // Mise à jour dynamique des champs
        newItemCategory.addEventListener('change', updateNewItemFields);

        function updateNewItemFields() {
            const category = newItemCategory.value;
            newItemCategoryInput.value = category;
            
            // Masquer tous les champs
            Object.values(categoryFieldsMap).forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            
            // Afficher les champs correspondants
            const fieldId = categoryFieldsMap[category];
            if (fieldId) {
                document.getElementById(fieldId).classList.remove('hidden');
            }
        }

        // Fermeture modal d'ajout
        [closeNewItemModal, cancelNewItem].forEach(element => {
            element.addEventListener('click', () => newItemModal.classList.add('hidden'));
        });
    });
</script>
</body>
</html>
