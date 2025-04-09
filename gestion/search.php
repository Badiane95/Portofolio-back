<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

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
    'skills' => 'Compétences'
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
    
    switch ($category) {
        case 'projects':
            $name = htmlspecialchars($data['name']);
            $description = htmlspecialchars($data['description']);
            $status = htmlspecialchars($data['status']);
            
            $stmt = $conn->prepare("UPDATE projects SET name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $description, $status, $id);
            break;
            
        case 'adherents':
            $nom = htmlspecialchars($data['nom']);
            $prenom = htmlspecialchars($data['prenom']);
            $email = htmlspecialchars($data['email']);
            
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
            $level = filter_var($data['level'], FILTER_VALIDATE_INT);
            
            $stmt = $conn->prepare("UPDATE skills SET name = ?, level = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $level, $id);
            break;
            
        default:
            $_SESSION['error'] = "Catégorie invalide!";
            header("Location: search.php");
            exit;
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Élément mis à jour avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour: " . $conn->error;
    }
    
    header("Location: search.php");
    exit;
}

/**
 * Fonction pour créer un nouvel élément
 */
function handleCreate($conn, $category, $data) {
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
            $email = htmlspecialchars($data['email']);
            
            $stmt = $conn->prepare("INSERT INTO adherents (nom, prenom, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $prenom, $email);
            break;
            
        case 'images':
            $title = htmlspecialchars($data['title']);
            $filename = $_FILES['image']['name'];
            $filepath = '/BUT2/S4/Portofolio-Back/lib/uploadPhoto/' . $filename;
            
            // Gérer l'upload de l'image
            move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $filepath);
            
            $stmt = $conn->prepare("INSERT INTO images (filename, filepath, title) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $filename, $filepath, $title);
            break;
            
        case 'skills':
            $name = htmlspecialchars($data['name']);
            $level = filter_var($data['level'], FILTER_VALIDATE_INT);
            
            $stmt = $conn->prepare("INSERT INTO skills (name, level) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $level);
            break;
            
        default:
            $_SESSION['error'] = "Catégorie invalide!";
            header("Location: search.php");
            exit;
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Élément créé avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de la création: " . $conn->error;
    }
    
    header("Location: search.php");
    exit;
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
            <form action="" method="GET" class="flex flex-wrap items-end space-y-4 md:space-y-0">
                <div class="w-full md:w-1/3 md:pr-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Terme de recherche</label>
                    <input type="text" id="search" name="search" value="<?php echo $search_term; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="w-full md:w-1/3 md:px-2">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select id="category" name="category" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <?php foreach ($categories as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($search_category === $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="w-full md:w-1/3 md:pl-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-search mr-2"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Bouton Ajouter un nouvel élément -->
        <div class="flex justify-end mb-6">
            <button id="newItemBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-plus mr-2"></i> Ajouter un nouvel élément
            </button>
        </div>
        
        <!-- Résultats de recherche -->
        <?php if (!empty($search_term)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Résultats de recherche pour "<?php echo $search_term; ?>"
                        <?php if ($search_category !== 'all'): ?>
                            dans <?php echo $categories[$search_category]; ?>
                        <?php endif; ?>
                    </h2>
                </div>
                
                <?php if (empty($results)): ?>
                    <div class="p-6 text-center text-gray-700">
                        Aucun résultat trouvé pour votre recherche.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom/Titre
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Catégorie
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($results as $item): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $item['id']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php 
                                                switch ($item['category']) {
                                                    case 'projects':
                                                        echo htmlspecialchars($item['name']);
                                                        break;
                                                    case 'adherents':
                                                        echo htmlspecialchars($item['nom']) . ' ' . htmlspecialchars($item['prenom']);
                                                        break;
                                                    case 'images':
                                                    case 'skills':
                                                        echo htmlspecialchars($item['name'] ?? $item['title'] ?? 'N/A');
                                                        break;
                                                }
                                                ?>
                                            </div>
                                            <?php if (isset($item['description'])): ?>
                                                <div class="text-sm text-gray-500 truncate max-w-xs">
                                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                  <?php 
                                                  switch ($item['category']) {
                                                      case 'projects': echo 'bg-blue-100 text-blue-800'; break;
                                                      case 'adherents': echo 'bg-green-100 text-green-800'; break;
                                                      case 'images': echo 'bg-purple-100 text-purple-800'; break;
                                                      case 'skills': echo 'bg-yellow-100 text-yellow-800'; break;
                                                  }
                                                  ?>">
                                                <?php echo $categories[$item['category']]; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            $date = isset($item['created_at']) ? $item['created_at'] : (isset($item['upload_date']) ? $item['upload_date'] : 'N/A');
                                            echo $date !== 'N/A' ? date('d/m/Y H:i', strtotime($date)) : 'N/A';
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="?action=edit&category=<?php echo $item['category']; ?>&id=<?php echo $item['id']; ?>" 
                                               class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <a href="?action=delete&category=<?php echo $item['category']; ?>&id=<?php echo $item['id']; ?>" 
                                               class="text-red-600 hover:text-red-900"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément?');">
                                                <i class="fas fa-trash-alt"></i> Supprimer
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
                    
                    <div id="projectFields" class="hidden">
                        <div class="mb-4">
                            <label for="projectName" class="block text-sm font-medium text-gray-700 mb-1">Nom du projet</label>
                            <input type="text" id="projectName" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="projectDescription" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="projectStatus" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select id="projectStatus" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                <option value="en cours">En cours</option>
                                <option value="terminé">Terminé</option>
                                <option value="planifié">Planifié</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="adherentFields" class="hidden">
                        <div class="mb-4">
                            <label for="adherentNom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                            <input type="text" id="adherentNom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="adherentPrenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                            <input type="text" id="adherentPrenom" name="prenom" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="adherentEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="adherentEmail" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div id="imageFields" class="hidden">
                        <div class="mb-4">
                            <label for="imageTitle" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                            <input type="text" id="imageTitle" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="imageFile" class="block text-sm font-medium text-gray-700 mb-1">Image (uniquement pour la création)</label>
                            <input type="file" id="imageFile" name="image" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div id="skillFields" class="hidden">
                        <div class="mb-4">
                            <label for="skillName" class="block text-sm font-medium text-gray-700 mb-1">Nom de la compétence</label>
                            <input type="text" id="skillName" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="skillLevel" class="block text-sm font-medium text-gray-700 mb-1">Niveau (1-100)</label>
                            <input type="range" id="skillLevel" name="level" min="1" max="100" class="w-full" oninput="document.getElementById('skillLevelValue').textContent = this.value">
                            <span id="skillLevelValue">50</span>%
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
                    </select>
                </div>
                
                <form id="newItemForm" method="POST" action="?action=create" enctype="multipart/form-data">
                    <input type="hidden" name="category" id="newItemCategoryInput">
                    
                    <div id="newProjectFields" class="hidden">
                        <!-- Champs pour un nouveau projet -->
                    </div>
                    
                    <div id="newAdherentFields" class="hidden">
                        <!-- Champs pour un nouvel adhérent -->
                    </div>
                    
                    <div id="newImageFields" class="hidden">
                        <!-- Champs pour une nouvelle image -->
                    </div>
                    
                    <div id="newSkillFields" class="hidden">
                        <!-- Champs pour une nouvelle compétence -->
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
    </div>
    
    <script>
        // Gestion des modals
        document.addEventListener('DOMContentLoaded', function() {
            // Éléments du DOM pour le modal d'édition
            const editModal = document.getElementById('editModal');
            const closeEditModal = document.getElementById('closeEditModal');
            const cancelEdit = document.getElementById('cancelEdit');
            const editLinks = document.querySelectorAll('a[href^="?action=edit"]');
            
            // Affichage du modal d'édition lors du clic sur "Modifier"
            editLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const category = url.searchParams.get('category');
                    const id = url.searchParams.get('id');
                    
                    // Faire une requête AJAX pour récupérer les données de l'élément
                    fetch(`get_item.php?category=${category}&id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            // Remplir le formulaire d'édition avec les données
                            document.getElementById('editId').value = id;
                            document.getElementById('editCategory').value = category;
                            
                            // Afficher uniquement les champs pertinents selon la catégorie
                            document.getElementById('projectFields').classList.add('hidden');
                            document.getElementById('adherentFields').classList.add('hidden');
                            document.getElementById('imageFields').classList.add('hidden');
                            document.getElementById('skillFields').classList.add('hidden');
                            
                            if (category === 'projects') {
                                document.getElementById('projectFields').classList.remove('hidden');
                                document.getElementById('projectName').value = data.name;
                                document.getElementById('projectDescription').value = data.description;
                                document.getElementById('projectStatus').value = data.status;
                            } else if (category === 'adherents') {
                                document.getElementById('adherentFields').classList.remove('hidden');
                                document.getElementById('adherentNom').value = data.nom;
                                document.getElementById('adherentPrenom').value = data.prenom;
                                document.getElementById('adherentEmail').value = data.email;
                            } else if (category === 'images') {
                                document.getElementById('imageFields').classList.remove('hidden');
                                document.getElementById('imageTitle').value = data.title;
                            } else if (category === 'skills') {
                                document.getElementById('skillFields').classList.remove('hidden');
                                document.getElementById('skillName').value = data.name;
                                document.getElementById('skillLevel').value = data.level;
                                document.getElementById('skillLevelValue').textContent = data.level;
                            }
                            
                            // Afficher le modal
                            editModal.classList.remove('hidden');
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            alert('Une erreur est survenue lors de la récupération des données');
                        });
                });
            });
            
            // Fermer le modal d'édition
            [closeEditModal, cancelEdit].forEach(element => {
                element.addEventListener('click', function() {
                    editModal.classList.add('hidden');
                });
            });
            
            // Éléments du DOM pour le modal d'ajout
            const newItemBtn = document.getElementById('newItemBtn');
            const newItemModal = document.getElementById('newItemModal');
            const closeNewItemModal = document.getElementById('closeNewItemModal');
            const cancelNewItem = document.getElementById('cancelNewItem');
            const newItemCategory = document.getElementById('newItemCategory');
            const newItemCategoryInput = document.getElementById('newItemCategoryInput');
            
            // Affichage du modal d'ajout
            newItemBtn.addEventListener('click', function() {
                newItemModal.classList.remove('hidden');
                updateNewItemFields();
            });
            
            // Mise à jour des champs selon la catégorie sélectionnée
            newItemCategory.addEventListener('change', updateNewItemFields);
            
            function updateNewItemFields() {
                const category = newItemCategory.value;
                newItemCategoryInput.value = category;
                
                document.getElementById('newProjectFields').classList.add('hidden');
                document.getElementById('newAdherentFields').classList.add('hidden');
                document.getElementById('newImageFields').classList.add('hidden');
                document.getElementById('newSkillFields').classList.add('hidden');
                
                document.getElementById(`new${category.charAt(0).toUpperCase() + category.slice(1, -1)}Fields`).classList.remove('hidden');
            }
            
            // Fermer le modal d'ajout
            [closeNewItemModal, cancelNewItem].forEach(element => {
                element.addEventListener('click', function() {
                    newItemModal.classList.add('hidden');
                });
            });
        });
    </script>
</body>
</html>
