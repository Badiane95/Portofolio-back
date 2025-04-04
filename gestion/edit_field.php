<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact_field'])) {
    $id = intval($_POST['id']);
    $data = [
        'field_type' => $conn->real_escape_string($_POST['field_type']),
        'label' => $conn->real_escape_string($_POST['label']),
        'placeholder' => $conn->real_escape_string($_POST['placeholder']),
        'options' => $conn->real_escape_string($_POST['options']),
        'is_required' => isset($_POST['is_required']) ? 1 : 0
    ];

    $stmt = $conn->prepare("UPDATE contact_form SET 
        field_type = ?, 
        label = ?, 
        placeholder = ?, 
        options = ?, 
        is_required = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssssii", 
        $data['field_type'],
        $data['label'],
        $data['placeholder'],
        $data['options'],
        $data['is_required'],
        $id);

    if($stmt->execute()) {
        $_SESSION['message'] = "Champ mis à jour avec succès !";
    } else {
        $_SESSION['error'] = "Erreur de mise à jour : " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Récupération du champ à éditer
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM contact_form WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$field = $stmt->get_result()->fetch_assoc();

if (!$field) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Édition de champ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="max-w-3xl mx-auto py-8">
    <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
        <h2 class="text-2xl font-bold text-purple-800 mb-6">Modifier le champ</h2>
        
        <form method="POST" action="edit_field.php" class="space-y-6">
            <input type="hidden" name="id" value="<?= $field['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type de champ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de champ</label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Libellé</label>
                    <input type="text" name="label" value="<?= htmlspecialchars($field['label']) ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Placeholder -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Placeholder</label>
                    <input type="text" name="placeholder" value="<?= htmlspecialchars($field['placeholder']) ?>"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Options (seulement pour select) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Options (séparées par des virgules)</label>
                    <textarea name="options" rows="3"
                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              <?= $field['field_type'] !== 'select' ? 'disabled' : '' ?>><?= htmlspecialchars($field['options']) ?></textarea>
                </div>
            </div>

            <!-- Options de bas de formulaire -->
            <div class="flex items-center justify-between border-t pt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_required" id="is_required" <?= $field['is_required'] ? 'checked' : '' ?>
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="is_required" class="ml-2 text-sm text-gray-600">Champ obligatoire</label>
                </div>
                
                <div class="space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                    <button type="submit" name="save_contact_field" 
                            class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
