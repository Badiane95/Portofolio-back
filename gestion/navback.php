<nav class="bg-purple-700 text-white shadow-lg">
    <!-- bg-purple-700 : Couleur de fond violet.
         text-white : Texte en blanc.
         shadow-lg : Ombre portée importante. -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- max-w-7xl : Largeur maximale du contenu.
             mx-auto : Centrage horizontal.
             px-4 sm:px-6 lg:px-8 : Padding horizontal variable selon la taille de l'écran. -->
        <div class="flex items-center justify-between h-20">
            <!-- flex : Utilisation de Flexbox pour la disposition.
                 items-center : Centrage vertical des éléments.
                 justify-between : Répartition de l'espace entre les éléments.
                 h-20 : Hauteur de 20 unités (peut correspondre à une valeur spécifique dans Tailwind). -->
            <div class="flex items-center flex-1">
                <!-- flex : Utilisation de Flexbox.
                     items-center : Centrage vertical.
                     flex-1 : Prend tout l'espace disponible. -->
                <a href="dashboard.php" class="flex-shrink-0 mr-8">
                    <!-- flex-shrink-0 : Empêche l'élément de se réduire.
                         mr-8 : Marge à droite de 8 unités. -->
                    <img class="h-10 w-10" src="../images/logo.svg" alt="Logo">
                    <!-- h-10 w-10 : Hauteur et largeur de 10 unités (définies dans Tailwind).
                         alt="Logo" : Texte alternatif pour l'image, important pour l'accessibilité. -->
                </a>
                <div class="flex-1">
                    <!-- flex-1 : Prend tout l'espace disponible. -->
                    <div class="ml-4 flex items-center space-x-6">
                        <!-- ml-4 : Marge à gauche de 4 unités.
                             flex : Utilisation de Flexbox.
                             items-center : Centrage vertical.
                             space-x-6 : Espacement horizontal entre les éléments de 6 unités. -->
                        <a href="dashboard.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <!-- px-4 py-3 : Padding horizontal et vertical.
                                 rounded-md : Bordures arrondies.
                                 text-sm : Taille du texte petite.
                                 font-medium : Police de caractères semi-grasse.
                                 hover:bg-purple-600 : Changement de couleur de fond au survol.
                                 transition duration-300 : Transition CSS d'une durée de 300ms.
                                 flex items-center gap-2 : Utilisation de Flexbox pour centrer verticalement et espacement entre l'icône et le texte. -->
                            <i class="fas fa-home"></i>
                            Accueil
                        </a>
                        <a href="add_adherent.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-user-plus"></i>
                            Adhérent
                        </a>
                        <a href="add_social_media.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-hashtag"></i>
                            Média Social
                        </a>
                        <a href="upload_image.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-image"></i>
                            Images
                        </a>
                        <a href="project.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-project-diagram"></i>
                            Projets
                        </a>
                        <a href="gestion_cv.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-file-alt"></i>
                            CV
                        </a>
                        <a href="add_video.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-video"></i>
                            Vidéo
                        </a>
                        <a href="add_field.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            Formulaire Contact
                        </a>
                        <a href="add_skill.php" class="px-4 py-3 rounded-md text-sm font-medium hover:bg-purple-600 transition duration-300 flex items-center gap-2">
                            <i class="fas fa-tools"></i>
                            Compétences
                        </a>
                        
                    </div>
                </div>
                <div class="ml-8">
                    <!-- ml-8 : Marge à gauche de 8 unités. -->
                    <a href='../login/logout.php' class="px-5 py-3 rounded-md text-sm font-medium bg-purple-600 hover:bg-purple-500 transition duration-300 flex items-center gap-2">
                        <!-- px-5 py-3 : Padding horizontal et vertical.
                             rounded-md : Bordures arrondies.
                             text-sm : Taille du texte petite.
                             font-medium : Police de caractères semi-grasse.
                             bg-purple-600 : Couleur de fond violet.
                             hover:bg-purple-500 : Changement de couleur de fond au survol.
                             transition duration-300 : Transition CSS d'une durée de 300ms.
                             flex items-center gap-2 : Utilisation de Flexbox pour centrer verticalement et espacement entre l'icône et le texte. -->
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
