<?php
session_start();
require __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données

// Vérification de l'authentification de l'administrateur
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php"); // Redirige vers la page de connexion si l'admin n'est pas connecté
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des CV</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navback.php'; ?>
    
    <!-- Section Gestion des CV -->
    <section class="mb-8">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="bg-white shadow-xl rounded-lg border border-purple-100">
                <!-- Entête -->
                <div class="p-6 border-b border-purple-200">
                    <h2 class="text-3xl font-bold text-purple-800">
                        <i class="fas fa-file-alt mr-2"></i>Gestion des CV
                    </h2>
                </div>

                <!-- Formulaire d'ajout -->
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-purple-700">
                            <i class="fas fa-plus-circle mr-2"></i>Ajouter un nouveau CV
                        </h3>

                        <?php if(isset($_SESSION['message'])): ?>
                            <div class="p-4 mb-4 text-green-800 bg-green-100 rounded-lg"><?= $_SESSION['message'] ?></div>
                            <?php unset($_SESSION['message']); // Supprime le message de la session pour ne pas l'afficher à nouveau ?>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="p-4 mb-4 text-red-800 bg-red-100 rounded-lg"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); // Supprime l'erreur de la session pour ne pas l'afficher à nouveau ?>
                        <?php endif; ?>
                    </div>

                    <!-- Formulaire d'ajout de CV -->
                    <form method="POST" action="add_cv.php" enctype="multipart/form-data" class="space-y-6">
                        <!-- Utilisation de la méthode POST pour envoyer les données -->
                        <!-- L'attribut enctype est nécessaire pour l'envoi de fichiers -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Utilisation de grid pour structurer le formulaire en deux colonnes sur les écrans plus grands -->

                            <!-- Colonne gauche -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du CV *</label>
                                    <input type="text" name="nom_cv" required
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                           placeholder="Ex: CV Développeur Fullstack">
                                    <!-- Champ de texte pour le nom du CV, requis -->
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichier CV *</label>
                                    <div class="relative">
                                        <input type="file" name="fileToUpload" required
                                               accept=".pdf,.docx"
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200"
                                               onchange="previewFileName(this)">
                                        <!-- Champ de type fichier pour uploader le CV, requis, accepte les formats PDF et DOCX -->
                                        <span class="text-xs text-gray-400 mt-2 block">Formats acceptés : PDF, DOCX (max 5MB)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Colonne droite -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="4"
                                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                              placeholder="Décrire le contenu du CV..."></textarea>
                                    <!-- Zone de texte pour la description du CV -->
                                </div>

                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="actif" id="actif"
                                           class="h-4 w-4 text-purple-600 rounded focus:ring-purple-500 border-gray-300">
                                    <!-- Checkbox pour activer/désactiver la visibilité publique du CV -->
                                    <label for="actif" class="text-sm text-gray-700">CV visible publiquement</label>
                                </div>
                            </div>
                        </div>
                        <!-- Boutons d'annulation et de soumission -->
                        <div class="flex justify-end border-t pt-6">
                            <div class="space-x-4">
                                <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                                <button type="submit"
                                        class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                                    Publier le CV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Liste des CV existants -->
                <?php
                $query = "SELECT * FROM cv ORDER BY date_ajout DESC"; // Requête pour sélectionner tous les CV, ordonnés par date d'ajout décroissante
                $result = $conn->query($query); // Exécution de la requête
                ?>

                <div class="px-8 pb-8">
                    <h3 class="text-xl font-semibold text-purple-700 mb-6">
                        <i class="fas fa-list-ul mr-2"></i>CV existants (<?= $result->num_rows ?>)
                        <!-- Affichage du nombre de CV existants -->
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-purple-100 shadow">
                        <!-- Table pour afficher la liste des CV -->
                        <table class="min-w-full divide-y divide-purple-200">
                            <thead class="bg-purple-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Date d'ajout</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-purple-200">
                                <?php while($cv = $result->fetch_assoc()): ?>
                                <!-- Boucle pour afficher chaque CV -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm rounded-full <?= $cv['actif'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                            <?= $cv['actif'] ? 'Actif' : 'Inactif' ?>
                                            <!-- Affichage du statut du CV (Actif/Inactif) -->
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($cv['nom_cv']) ?></td>
                                    <!-- Affichage du nom du CV -->
                                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($cv['date_ajout'])) ?></td>
                                    <!-- Affichage de la date d'ajout du CV, formatée -->
                                    <td class="px-6 py-4 text-sm space-x-4">
                                        <!-- Actions possibles pour chaque CV -->
                                        <a href="download_cv.php?id=<?= $cv['id'] ?>" class="text-purple-600 hover:text-purple-900" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                            <!-- Lien pour télécharger le CV -->
                                        </a>
                                        <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="text-blue-600 hover:text-blue-900" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                            <!-- Lien pour modifier le CV -->
                                        </a>
                                        <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="text-red-600 hover:text-red-900" title="Supprimer"
                                           onclick="return confirm('Supprimer définitivement ce CV ?')">
                                            <i class="fas fa-trash"></i>
                                            <!-- Lien pour supprimer le CV, avec confirmation -->
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Fonction pour afficher le nom du fichier sélectionné
    function previewFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name; // Récupère le nom du fichier
            //input.parentNode.querySelector('.file-name').textContent = fileName;
        }
    }
    </script>
</body>
</html>
