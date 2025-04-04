<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <section class="space-y-8">
    <?php include 'navback.php'; ?>
        <!-- Formulaire d'édition/ajout -->
        <div class="bg-gradient-to-br from-purple-50 to-white rounded-2xl shadow-xl p-8 border border-purple-100">
            <h2 class="text-2xl font-bold bg-purple-600 text-white px-4 py-3 rounded-t-lg -m-8 mb-6 shadow-md">
                <?= isset($edit_video) ? 'Modifier la vidéo' : 'Ajouter une nouvelle vidéo' ?>
            </h2>
            
            <form method="POST" action="video_operations.php" class="space-y-6">
                <?php if (isset($edit_video)): ?>
                    <input type="hidden" name="id" value="<?= $edit_video['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-purple-700">Titre de la vidéo</label>
                        <input type="text" name="title" required 
                            value="<?= isset($edit_video) ? htmlspecialchars($edit_video['title']) : '' ?>"
                            class="w-full px-4 py-3 border-2 border-purple-100 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-purple-700">URL YouTube (embed)</label>
                        <input type="url" name="video_url" 
                            pattern="https://www\.youtube\.com/embed/.+" 
                            value="<?= isset($edit_video) ? htmlspecialchars($edit_video['video_url']) : '' ?>"
                            class="w-full px-4 py-3 border-2 border-purple-100 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                            placeholder="https://www.youtube.com/embed/ID_VIDEO">
                    </div>
                </div>
    
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-purple-700">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-3 border-2 border-purple-100 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"><?= 
                            isset($edit_video) ? htmlspecialchars($edit_video['description']) : '' ?></textarea>
                </div>
    
                <button type="submit" name="<?= isset($edit_video) ? 'update_video' : 'add_video' ?>" 
                    class="w-full lg:w-auto px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
                    <?= isset($edit_video) ? 'Mettre à jour' : 'Ajouter la vidéo' ?>
                </button>
            </form>
        </div>
</body>
</html>