<?php
// Démarrage de la session pour gérer les messages utilisateur
session_start();
// Inclusion sécurisée du fichier de connexion MySQL
include __DIR__ . '/../connexion/msql.php';

// Traitement du formulaire en POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation et sécurisation des entrées
    $id = intval($_POST['id']); // Protection contre l'injection SQL
    $nom = $conn->real_escape_string($_POST['nom']); // Échappement des caractères spéciaux
    $link = $conn->real_escape_string($_POST['link']); // Idem pour le lien

    // Requête préparée pour plus de sécurité
    $stmt = $conn->prepare("UPDATE social_media SET nom=?, link=? WHERE id=?");
    $stmt->bind_param("ssi", $nom, $link, $id);
    
    // Exécution avec gestion des erreurs
    if ($stmt->execute()) {
        $_SESSION['message'] = "Modification réussie";
    } else {
        $_SESSION['error'] = "Erreur de modification: " . $stmt->error; // Ajout du message d'erreur SQL
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

// Récupération des données existantes
$media = [];
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sécurisation de l'ID
    
    // Amélioration recommandée : Utiliser une requête préparée ici aussi
    // $stmt = $conn->prepare("SELECT * FROM social_media WHERE id = ?");
    // $stmt->bind_param("i", $id);
    // $stmt->execute();
    // $media = $stmt->get_result()->fetch_assoc();
    
    $result = $conn->query("SELECT * FROM social_media WHERE id = $id");
    $media = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un média social</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-xl rounded-lg border border-purple-100">
            <div class="p-8">
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-hashtag mr-2"></i>Modifier le réseau social
                </h2>

                <!-- Formulaire de modification -->
                <form method="POST" action="edit_social_media.php" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $media['id'] ?>">

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Champ Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du réseau *</label>
                            <input type="text" name="nom" 
                                   value="<?= htmlspecialchars($media['nom']) ?>" 
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Ex: LinkedIn, GitHub...">
                        </div>

                        <!-- Champ Lien -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lien du profil *</label>
                            <input type="url" name="link" 
                                   value="<?= htmlspecialchars($media['link']) ?>" 
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="https://...">
                        </div>
                    </div>

                    <!-- Section des boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                            <button type="submit" 
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
<?php
// Fermeture de la connexion MySQL
$conn->close();
?>
