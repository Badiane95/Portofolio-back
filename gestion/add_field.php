<?php
session_start(); // Démarre ou reprend une session existante

include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Traitement du formulaire si la méthode est POST et le bouton "add_field" est cliqué
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    // Récupération et nettoyage des données du formulaire pour éviter les injections SQL
    $data = [
        'field_name' => $conn->real_escape_string($_POST['field_name']), // Nom technique du champ
        'field_type' => $conn->real_escape_string($_POST['field_type']), // Type de champ (text, email, select, textarea)
        'label' => $conn->real_escape_string($_POST['label']), // Libellé visible du champ
        'placeholder' => $conn->real_escape_string($_POST['placeholder']), // Texte d'aide à la saisie
        'options' => $conn->real_escape_string($_POST['options']), // Options pour les listes déroulantes
        'is_required' => isset($_POST['is_required']) ? 1 : 0, // Indique si le champ est obligatoire (1) ou non (0)
        'display_order' => intval($_POST['display_order']) // Ordre d'affichage du champ dans le formulaire
    ];

    // Préparation de la requête SQL pour insérer les données dans la table "contact_form"
    $stmt = $conn->prepare("INSERT INTO contact_form 
        (field_name, field_type, label, placeholder, options, is_required, display_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Liaison des paramètres à la requête préparée
    $stmt->bind_param("sssssii", 
        $data['field_name'], // Nom technique du champ
        $data['field_type'], // Type de champ
        $data['label'], // Libellé visible
        $data['placeholder'], // Placeholder
        $data['options'], // Options (pour les listes déroulantes)
        $data['is_required'], // Champ obligatoire (1 ou 0)
        $data['display_order']); // Ordre d'affichage

    // Exécution de la requête
    if($stmt->execute()) {
        $_SESSION['message'] = "Champ ajouté avec succès !"; // Enregistre un message de succès dans la session
    } else {
        $_SESSION['error'] = "Erreur : " . $stmt->error; // Enregistre un message d'erreur dans la session
    }
    
    $stmt->close(); // Fermeture de la requête préparée
    header("Location: dashboard.php"); // Redirection vers le tableau de bord
    exit(); // Termine l'exécution du script
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un champ</title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; // Inclusion du menu de navigation ?>
    
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
           <div class="p-8">
                <!-- Titre de la page -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-envelope mr-2"></i>Ajouter un nouveau champ
                </h2>
            <!-- Formulaire pour ajouter un nouveau champ -->
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom du champ -->
                    <div>
                        <!-- Label pour le nom du champ -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom technique</label>
                        <!-- Input pour le nom du champ -->
                        <input type="text" name="field_name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: email, message...">
                    </div>

                    <!-- Type de champ -->
                    <div>
                        <!-- Label pour le type de champ -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de champ</label>
                        <!-- Select pour le type de champ -->
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
                        <!-- Label pour le libellé -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Libellé visible</label>
                        <!-- Input pour le libellé -->
                        <input type="text" name="label" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: Votre email">
                    </div>

                    <!-- Placeholder -->
                    <div>
                        <!-- Label pour le placeholder -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Placeholder</label>
                        <!-- Input pour le placeholder -->
                        <input type="text" name="placeholder"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ex: exemple@email.com">
                    </div>

                    <!-- Options -->
                    <div class="md:col-span-2">
                        <!-- Label pour les options -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Options (pour les listes déroulantes)</label>
                        <!-- Textarea pour les options -->
                        <textarea name="options" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="Séparer les options par des virgules (Ex: Option 1, Option 2)"></textarea>
                    </div>

                    <!-- Ordre d'affichage -->
                    <div>
                        <!-- Label pour l'ordre d'affichage -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ordre d'affichage</label>
                        <!-- Input pour l'ordre d'affichage -->
                        <input type="number" name="display_order" required min="1" value="1"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <!-- Obligatoire -->
                    <div class="flex items-center mt-4">
                        <!-- Checkbox pour indiquer si le champ est obligatoire -->
                        <input type="checkbox" name="is_required" id="is_required" checked
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <!-- Label pour le checkbox "Obligatoire" -->
                        <label for="is_required" class="ml-2 text-sm text-gray-600">Champ obligatoire</label>
                    </div>
                </div>

                <!-- Section pour les boutons "Annuler" et "Créer le champ" -->
                <div class="flex justify-end border-t pt-6">
                    <div class="space-x-4">
                        <!-- Lien pour annuler et revenir au tableau de bord -->
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                        <!-- Bouton pour soumettre le formulaire et créer le champ -->
                        <button type="submit" name="add_field"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Créer le champ
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</body>
</html>
