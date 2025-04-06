<?php
session_start(); // Démarre ou reprend une session existante

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact_field'])) {
    $id = intval($_POST['id']); // Récupère et valide l'ID du champ à modifier
    $data = [
        'field_type' => $conn->real_escape_string($_POST['field_type']), // Type de champ
        'label' => $conn->real_escape_string($_POST['label']), // Libellé du champ
        'placeholder' => $conn->real_escape_string($_POST['placeholder']), // Placeholder du champ
        'options' => $conn->real_escape_string($_POST['options']), // Options du champ (pour les listes déroulantes)
        'is_required' => isset($_POST['is_required']) ? 1 : 0 // Indique si le champ est requis
    ];

    // Prépare la requête SQL pour mettre à jour le champ
    $stmt = $conn->prepare("UPDATE contact_form SET 
        field_type = ?, 
        label = ?, 
        placeholder = ?, 
        options = ?, 
        is_required = ? 
        WHERE id = ?");
    
    // Lie les paramètres à la requête préparée
    $stmt->bind_param("ssssii", 
        $data['field_type'], // Type de champ
        $data['label'], // Libellé
        $data['placeholder'], // Placeholder
        $data['options'], // Options
        $data['is_required'], // Requis
        $id // ID du champ à modifier
    );

    // Exécute la requête
    if($stmt->execute()) {
        $_SESSION['message'] = "Champ mis à jour avec succès !"; // Message de succès
    } else {
        $_SESSION['error'] = "Erreur de mise à jour : " . $stmt->error; // Message d'erreur
    }
    
    $stmt->close(); // Ferme la requête préparée
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit(); // Termine l'exécution du script
}

// Récupération du champ à éditer
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Récupère l'ID du champ à éditer depuis les paramètres GET
$stmt = $conn->prepare("SELECT * FROM contact_form WHERE id = ?"); // Prépare la requête SQL
$stmt->bind_param("i", $id); // Lie l'ID à la requête préparée
$stmt->execute(); // Exécute la requête
$field = $stmt->get_result()->fetch_assoc(); // Récupère le résultat de la requête

// Si le champ n'est pas trouvé, redirige vers le tableau de bord
if (!$field) {
    header("Location: dashboard.php"); // Redirige vers le tableau de bord
    exit(); // Termine l'exécution du script
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Édition de champ</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration du script pour la zone de dépôt (dropZone.js) -->
    <script src="dropZone.js" defer></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
<?php include 'navback.php'; // Inclusion du menu de navigation ?>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-edit mr-2"></i>Modifier le champ
                </h2>

                <!-- Formulaire de modification du champ -->
                <form method="POST" action="edit_field.php" class="space-y-6">
                    <!-- Input caché pour l'ID du champ -->
                    <input type="hidden" name="id" value="<?= $field['id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type de champ -->
                        <div>
                            <!-- Label pour le type de champ -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de champ *</label>
                            <!-- Select pour le type de champ -->
                            <select name="field_type" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="text" <?= $field['field_type'] === 'text' ? 'selected' : '' ?>>Texte</option>
                                <option value="email" <?= $field['field_type'] === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="select" <?= $field['field_type'] === 'select' ? 'selected' : '' ?>>Liste déroulante</option>
                                <option value="textarea" <?= $field['field_type'] === 'textarea' ? 'selected' : '' ?>>Zone de texte</option>
                            </select>
                        </div>

                        <!-- Libellé -->
                        <div>
                            <!-- Label pour le libellé -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Libellé *</label>
                            <!-- Input pour le libellé -->
                            <input type="text" name="label" 
                                   value="<?= htmlspecialchars($field['label']) ?>" 
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Placeholder -->
                        <div>
                            <!-- Label pour le placeholder -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Placeholder</label>
                            <!-- Input pour le placeholder -->
                            <input type="text" name="placeholder" 
                                   value="<?= htmlspecialchars($field['placeholder']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Options -->
                        <div class="md:col-span-2">
                            <!-- Label pour les options -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Options (séparées par des virgules)
                                <span class="text-xs text-gray-400 ml-1">(uniquement pour les listes déroulantes)</span>
                            </label>
                            <!-- Textarea pour les options -->
                            <textarea name="options" rows="3"
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                      <?= $field['field_type'] !== 'select' ? 'disabled' : '' ?>><?= htmlspecialchars($field['options']) ?></textarea>
                        </div>
                    </div>

                    <!-- Options de bas de formulaire -->
                    <div class="flex flex-col md:flex-row justify-between border-t pt-6 space-y-4 md:space-y-0">
                        <!-- Champ obligatoire -->
                        <div class="flex items-center">
                            <!-- Checkbox pour le champ obligatoire -->
                            <input type="checkbox" 
                                   name="is_required" 
                                   id="is_required" 
                                   <?= $field['is_required'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <!-- Label pour le champ obligatoire -->
                            <label for="is_required" class="ml-2 text-sm text-gray-600">Champ obligatoire</label>
                        </div>
                        
                        <!-- Boutons Annuler et Enregistrer -->
                        <div class="flex items-center space-x-4">
                            <!-- Lien Annuler -->
                            <a href="dashboard.php" 
                               class="text-gray-600 hover:text-gray-800 transition-colors">
                                Annuler
                            </a>
                            <!-- Bouton Enregistrer -->
                            <button type="submit" 
                                    name="save_contact_field" 
                                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
