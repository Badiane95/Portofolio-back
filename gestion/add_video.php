<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($edit_video) ? 'Modifier vidéo' : 'Nouvelle vidéo' ?></title>
    <!-- Intégration de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Intégration de Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <section class="space-y-8">
        <?php include 'navback.php'; // Inclusion du menu de navigation ?>
        
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white shadow-xl rounded-lg p-8 border border-purple-100">
                <!-- Titre de la page: "Modifier la vidéo" ou "Ajouter une vidéo" -->
                <h2 class="text-2xl font-bold text-purple-800 mb-6">
                    <i class="fas fa-video mr-2"></i>
                    <?= isset($edit_video) ? 'Modifier la vidéo' : 'Ajouter une vidéo' ?>
                </h2>

                <!-- Formulaire pour ajouter ou modifier une vidéo -->
                <form method="POST" action="video_operations.php" class="space-y-6">
                    <?php if (isset($edit_video)): ?>
                        <!-- Champ caché pour stocker l'ID de la vidéo en cas de modification -->
                        <input type="hidden" name="id" value="<?= $edit_video['id'] ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Titre -->
                        <div>
                            <!-- Label pour le titre -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                            <!-- Input pour le titre -->
                            <input type="text" name="title" required 
                                value="<?= isset($edit_video) ? htmlspecialchars($edit_video['title']) : '' ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Titre explicite...">
                        </div>

                        <!-- URL YouTube -->
                        <div>
                            <!-- Label pour l'URL YouTube -->
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL Embed YouTube</label>
                            <!-- Input pour l'URL YouTube -->
                            <input type="url" name="video_url" 
                                pattern="https://www\.youtube\.com/embed/.+" 
                                value="<?= isset($edit_video) ? htmlspecialchars($edit_video['video_url']) : '' ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="https://www.youtube.com/embed/ID_VIDEO">
                            <!-- Pattern attribute enforces correct YouTube embed URL format -->
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <!-- Label pour la description -->
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <!-- Textarea pour la description -->
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"><?= 
                                isset($edit_video) ? htmlspecialchars($edit_video['description']) : '' ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="flex justify-end border-t pt-6">
                        <div class="space-x-4">
                            <!-- Lien pour annuler et revenir au tableau de bord -->
                            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Annuler</a>
                            <!-- Bouton pour soumettre le formulaire (ajouter ou mettre à jour) -->
                            <button type="submit" name="<?= isset($edit_video) ? 'update_video' : 'add_video' ?>" 
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas <?= isset($edit_video) ? 'fa-sync' : 'fa-plus' ?> mr-2"></i>
                                <?= isset($edit_video) ? 'Mettre à jour' : 'Ajouter' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
