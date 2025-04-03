<footer id="footer" class="bg-gradient-to-r from-purple-800 to-indigo-900 text-white py-12 px-6">
    <div class="container mx-auto grid md:grid-cols-2 gap-8">
        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-purple-200">À Propos de Mon Portfolio</h2>
            <p class="text-gray-300 leading-relaxed">
                Passionné par le développement web et actuellement étudiant en BUT MMI, je suis constamment à la recherche de défis technologiques qui me permettent de développer mes compétences et de créer des solutions innovantes.
            </p>
        </section>
        
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-purple-200 mb-4">Réseaux Sociaux</h2>
                <ul class="flex space-x-4">
                    <?php
                    
                    include __DIR__ . '/../connexion/msql.php';
                    // Connexion à la base de données (assurez-vous que $conn est déjà établie)
                    $sql = "SELECT nom, link FROM social_media";
                    $result = $conn->query($sql);

                    $socialColors = [
                        'linkedin' => 'hover:bg-blue-600',
                        'github' => 'hover:bg-gray-700',
                        'behance' => 'hover:bg-blue-800'
                    ];

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $nom = strtolower($row['nom']);
                            $hoverClass = $socialColors[$nom] ?? 'hover:bg-purple-600';
                            echo '<li>
                                <a href="' . htmlspecialchars($row['link']) . '" 
                                   target="_blank" 
                                   class="text-white bg-purple-600 p-3 rounded-full inline-block transition duration-300 ease-in-out transform hover:scale-110 ' . $hoverClass . '">
                                    <i class="fab fa-' . htmlspecialchars($nom) . ' text-xl"></i>
                                </a>
                            </li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </section>
    </div>

    <div class="text-center mt-8 pt-6 border-t border-purple-700">
        <p class="text-sm text-gray-400">
            &copy; <?php echo date('Y'); ?> Falou Badiane. 
            Portfolio développé dans le cadre du BUT MMI.
        </p>
    </div>
</footer>

