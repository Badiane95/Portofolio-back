<footer id="footer" class="bg-purple-800 text-white py-12 px-6 shadow-xl">
    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-6">
        <!-- Section À Propos -->
        <section class="space-y-4 p-6 bg-white/5 rounded-lg border border-purple-200/20">
            <h2 class="text-2xl font-bold text-purple-200 border-l-4 border-purple-500 pl-4">
                À Propos de Mon Portfolio
            </h2>
            <p class="text-gray-300 leading-relaxed">
                Passionné par le développement web et actuellement étudiant en BUT MMI, je suis constamment à la recherche de défis technologiques qui me permettent de développer mes compétences et de créer des solutions innovantes.
            </p>
        </section>

        <!-- Section Réseaux Sociaux -->
        <section class="p-6 bg-white/5 rounded-lg border border-purple-200/20">
            <h2 class="text-2xl font-bold text-purple-200 border-l-4 border-purple-500 pl-4 mb-6">
                Réseaux Sociaux
            </h2>
            <ul class="flex space-x-4">
                <?php
                $sql = "SELECT nom, link FROM social_media";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $nom = strtolower($row['nom']);
                        echo '<li>
                            <a href="' . htmlspecialchars($row['link']) . '" 
                               target="_blank" 
                               class="text-white bg-purple-600 p-4 rounded-lg inline-block transition-colors hover:bg-purple-700 shadow-sm">
                                <i class="fab fa-' . htmlspecialchars($nom) . ' text-xl w-6 h-6 flex items-center justify-center"></i>
                            </a>
                        </li>';
                    }
                }
                ?>
            </ul>
        </section>
    </div>

    <!-- Copyright -->
    <div class="text-center mt-8 pt-6 border-t border-purple-200/20">
        <p class="text-sm text-gray-400">
            &copy; <?= date('Y') ?> Falou Badiane<br>
            <span class="text-xs">Portfolio développé dans le cadre du BUT MMI</span>
        </p>
    </div>
</footer>