<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de contenus</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
            <!-- Section de gestion du contenu de la page d'accueil -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-purple-50">
                <h2 class="text-2xl font-bold text-purple-700 mb-6 border-l-4 border-purple-500 pl-4">Gestion du contenu principal</h2>

                <form method="POST" action="update_content.php" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Section Header -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">En-tête</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="header_title" id="header_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       value="<?= htmlspecialchars($home_data['header_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Sous-titre</label>
                                <input type="text" name="header_subtitle" id="header_subtitle" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['header_subtitle'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Introduction -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Introduction</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="intro_title" id="intro_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['intro_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="intro_text" id="intro_text" rows="3"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['intro_text'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Première Section -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Première Section</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="first_title" id="first_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_title'] ?? '') ?>">
                            </div>

                            <!-- Item 1 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 1</label>
                                <input type="text" name="first_item1_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item1_title'] ?? '') ?>">
                                <input type="text" name="first_item1_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item1_text'] ?? '') ?>">
                            </div>

                            <!-- Item 2 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 2</label>
                                <input type="text" name="first_item2_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item2_title'] ?? '') ?>">
                                <input type="text" name="first_item2_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item2_text'] ?? '') ?>">
                            </div>

                            <!-- Item 3 -->
                            <div class="space-y-2 pl-4 border-l-2 border-purple-200">
                                <label class="block text-sm font-medium text-purple-700">Élément 3</label>
                                <input type="text" name="first_item3_title" placeholder="Titre" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item3_title'] ?? '') ?>">
                                <input type="text" name="first_item3_text" placeholder="Texte" 
                                       class="w-full p-2 border border-purple-100 rounded-md"
                                       value="<?= htmlspecialchars($home_data['first_item3_text'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section À propos -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section À propos</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="about_title" id="about_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['about_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="about_text" id="about_text" rows="3"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['about_text'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Section Projets -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Projets</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="projects_title" id="projects_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['projects_title'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Galerie -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Galerie</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="gallery_title" id="gallery_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['gallery_title'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Section Seconde Section -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Seconde Section</h3>

                            <!-- Titre et Texte -->
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre principal</label>
                                <input type="text" name="second_title" class="w-full p-2 border border-purple-200 rounded-md" 
                                       value="<?= htmlspecialchars($home_data['second_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte d'introduction</label>
                                <textarea name="second_text" rows="2" class="w-full p-2 border border-purple-200 rounded-md"><?= 
                                    htmlspecialchars($home_data['second_text'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Section Statistiques -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Statistiques</h3>

                            <!-- Prévisualisation en temps réel -->
                            <div class="mb-6 p-4 border border-purple-200 rounded-lg">
                                <h4 class="text-md font-semibold text-purple-600 mb-2">Aperçu</h4>
                                <div class="grid grid-cols-5 gap-4">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <div class="text-center p-3 bg-white rounded-lg shadow">
                                        <i class="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?> text-3xl text-purple-600 mb-2"></i>
                                        <div class="font-bold text-xl"><?= htmlspecialchars($home_data["second_stat{$i}_number"] ?? '') ?></div>
                                        <div class="text-sm"><?= htmlspecialchars($home_data["second_stat{$i}_label"] ?? '') ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Champs de formulaire améliorés -->
                            <?php for($i = 1; $i <= 5; $i++): ?>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <div class="flex items-center mb-3">
                                    <span class="text-purple-600 mr-2">#<?= $i ?></span>
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_icon" 
                                           class="flex-1 p-2 border-b-2 border-purple-100 focus:border-purple-500" 
                                           placeholder="Classe Font Awesome (ex: fa-users)"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_icon"] ?? '') ?>">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_number" 
                                           class="p-2 border rounded-lg" 
                                           placeholder="Nombre"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_number"] ?? '') ?>">
                                    
                                    <input type="text" 
                                           name="second_stat<?= $i ?>_label" 
                                           class="p-2 border rounded-lg" 
                                           placeholder="Libellé"
                                           value="<?= htmlspecialchars($home_data["second_stat{$i}_label"] ?? '') ?>">
                                </div>
                            </div>
                            <?php endfor; ?>

                            <!-- Contenu principal -->
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Contenu détaillé</label>
                                <textarea name="second_content" rows="5" class="w-full p-2 border border-purple-200 rounded-md"><?= 
                                    htmlspecialchars($home_data['second_content'] ?? '') ?></textarea>
                            </div>

                            <!-- Bouton -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-purple-700 mb-1">Texte du bouton</label>
                                    <input type="text" name="second_button_text" class="w-full p-2 border border-purple-200 rounded-md" 
                                           value="<?= htmlspecialchars($home_data['second_button_text'] ?? '') ?>">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-purple-700 mb-1">Lien du bouton</label>
                                    <input type="url" name="second_button_link" class="w-full p-2 border border-purple-200 rounded-md" 
                                           value="<?= htmlspecialchars($home_data['second_button_link'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Section CTA -->
                        <div class="space-y-4 p-4 bg-purple-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-600">Section Appel à l'action</h3>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Titre</label>
                                <input type="text" name="cta_title" id="cta_title" 
                                       class="w-full p-2 border border-purple-200 rounded-md"
                                       value="<?= htmlspecialchars($home_data['cta_title'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-700 mb-1">Texte</label>
                                <textarea name="cta_text" id="cta_text" rows="2"
                                          class="w-full p-2 border border-purple-200 rounded-md"><?= htmlspecialchars($home_data['cta_text'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="mt-8">
                        <button type="submit" 
                                class="w-full md:w-auto px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/>
                            </svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
            
           


</body>
</html>