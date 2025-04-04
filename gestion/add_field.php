<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    $data = [
        'field_name' => $conn->real_escape_string($_POST['field_name']),
        'field_type' => $conn->real_escape_string($_POST['field_type']),
        'label' => $conn->real_escape_string($_POST['label']),
        'placeholder' => $conn->real_escape_string($_POST['placeholder']),
        'options' => $conn->real_escape_string($_POST['options']),
        'is_required' => isset($_POST['is_required']) ? 1 : 0,
        'display_order' => intval($_POST['display_order'])
    ];

    $stmt = $conn->prepare("INSERT INTO contact_form 
        (field_name, field_type, label, placeholder, options, is_required, display_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssii", 
        $data['field_name'],
        $data['field_type'],
        $data['label'],
        $data['placeholder'],
        $data['options'],
        $data['is_required'],
        $data['display_order']);

    if($stmt->execute()) {
        $_SESSION['message'] = "Champ ajouté avec succès !";
    } else {
        $_SESSION['error'] = "Erreur : " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un champ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
            <h2 class="text-2xl font-bold text-purple-800 mb-6">Ajouter un nouveau champ</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom du champ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom technique</label>
                        <input type="text" name="field_name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: email, message...">
                    </div>

                    <!-- Type de champ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de champ</label>
                        <select name="field_type" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sélectionner un type</option>
                            <option value="text">Texte</option>
                            <option value="email">Email</option>
                            <option value="select">Liste déroulante</option>
                            <option value="textarea">Zone de texte</option>
                        </select>
                    </div>

                    <!-- Libellé -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Libellé visible</label>
                        <input type="text" name="label" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: Votre email">
                    </div>

                    <!-- Placeholder -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Placeholder</label>
                        <input type="text" name="placeholder"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: exemple@email.com">
                    </div>

                    <!-- Options -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Options (pour les listes déroulantes)</label>
                        <textarea name="options" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="Séparer les options par des virgules (Ex: Option 1, Option 2)"></textarea>
                    </div>

                    <!-- Ordre d'affichage -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ordre d'affichage</label>
                        <input type="number" name="display_order" required min="1" value="1"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <!-- Obligatoire -->
                    <div class="flex items-center mt-4">
                        <input type="checkbox" name="is_required" id="is_required" checked
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="is_required" class="ml-2 text-sm text-gray-600">Champ obligatoire</label>
                    </div>
                </div>

                <div class="flex justify-end border-t pt-6">
                    <div class="space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                        <button type="submit" name="add_field"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Créer le champ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>