<!DOCTYPE HTML>
<!--
    Stellar by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
    <head>
        <title>Mon Cv</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        
        <section id="cv-section" class="min-h-screen py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Titre principal centré -->
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-violet-700 mb-4">
                        <?= htmlspecialchars($home_content['cta_title'] ?? 'Mes CV') ?>
                    </h2>
                    <p class="text-lg text-violet-500 max-w-2xl mx-auto">
                        <?= htmlspecialchars($home_content['cta_text'] ?? 'Consultez mes CV en ligne ou téléchargez-les') ?>
                    </p>
                </div>

                <?php
                $uploadBaseUrl = '';
                include __DIR__ . '/connexion/msql.php';
                $sql = "SELECT * FROM cv WHERE actif = 1 ORDER BY date_ajout DESC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php while($cv = $result->fetch_assoc()): 
                            $fullPath = $uploadBaseUrl . htmlspecialchars($cv['chemin_cv']);
                        ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
                                <!-- En-tête du CV -->
                                <div class="p-6 bg-violet-100">
                                    <h3 class="text-xl font-semibold text-violet-800">
                                        <?= htmlspecialchars($cv['nom_cv']) ?>
                                    </h3>
                                    <p class="text-sm text-violet-500 mt-1">
                                        Ajouté le <?= date('d/m/Y', strtotime($cv['date_ajout'])) ?>
                                    </p>
                                </div>

                                <!-- Contenu principal -->
                                <div class="p-6 flex flex-col items-center">
                                    <!-- Prévisualisation PDF -->
                                    <div class="w-full h-96 mb-4 rounded-lg overflow-hidden border border-violet-100">
                                        <iframe 
                                            src="<?= $fullPath ?>#view=fitH" 
                                            class="w-full h-full"
                                            frameborder="0"
                                            title="Aperçu du CV <?= htmlspecialchars($cv['nom_cv']) ?>">
                                        </iframe>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="flex gap-4 w-full justify-center">
                                        <a href="<?= $fullPath ?>" 
                                           target="_blank"
                                           class="px-6 py-2 bg-violet-600 text-white rounded-full hover:bg-violet-700 transition-colors flex items-center">
                                            <i class="fas fa-expand mr-2"></i>
                                            Plein écran
                                        </a>
                                        <a href="<?= $fullPath ?>" 
                                           download="<?= htmlspecialchars($cv['nom_cv']) ?>"
                                           class="px-6 py-2 border-2 border-violet-600 text-violet-600 rounded-full hover:bg-violet-50 transition-colors flex items-center">
                                            <i class="fas fa-download mr-2"></i>
                                            Télécharger
                                        </a>
                                    </div>

                                    <?php if(!empty($cv['description'])): ?>
                                        <p class="mt-4 text-gray-600 text-center max-w-md">
                                            <?= nl2br(htmlspecialchars($cv['description'])) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="inline-flex items-center p-4 bg-violet-100 rounded-xl">
                            <i class="fas fa-file-pdf text-violet-600 text-2xl mr-3"></i>
                            <p class="text-violet-600">Aucun CV disponible pour le moment</p>
                        </div>
                    </div>
                <?php endif; 
                $conn->close();
                ?>
            </div>
            <div class="container mx-auto px-4 py-4">
        <a 
            href="index.php" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
            <i class="fas fa-home mr-2"></i> Retour à l'accueil
        </a>
    </div>
       
    </body>
</html>