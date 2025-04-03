
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des CV</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php
session_start();
require __DIR__ . '/../connexion/msql.php'; // Include the database connection file

if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des CV</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'navback.php'; ?>
    <!-- Section Gestion des CV -->
    <section class="mb-8">
        <div class="bg-white rounded-lg shadow-md">
            <!-- Entête -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800">Gestion des CV</h2>
            </div>

            <!-- Formulaire d'ajout -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-purple-600 mb-4">Ajouter un nouveau CV</h3>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" action="add_cv.php" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV *</label>
                                <input type="text" name="nom_cv" required
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="Ex: CV Développeur Fullstack">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fichier CV *</label>
                                <div class="relative">
                                    <input type="file" name="fileToUpload" required
                                           accept=".pdf,.docx"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                                           onchange="previewFileName(this)">
                                    <span class="text-xs text-gray-400 mt-1 block">Formats acceptés : PDF, DOCX (max 5MB)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="4"
                                          class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-purple-500"
                                          placeholder="Décrire le contenu du CV..."></textarea>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="actif" id="actif"
                                       class="h-4 w-4 text-purple-600 rounded focus:ring-purple-500">
                                <label for="actif" class="text-sm text-gray-700">CV visible publiquement</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-all">
                            <i class="fas fa-cloud-upload-alt mr-2"></i>
                            Publier le CV
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des CV existants -->
            <?php
            $query = "SELECT * FROM cv ORDER BY date_ajout DESC";
            $result = $conn->query($query);
            ?>

            <div class="px-6 pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">CV existants (<?= $result->num_rows ?>)</h3>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Nom</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Date d'ajout</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($cv = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $cv['actif'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                                        <?= $cv['actif'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($cv['nom_cv']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($cv['date_ajout'])) ?></td>
                                <td class="px-4 py-3 text-sm space-x-2">
                                    <a href="download_cv.php?id=<?= $cv['id'] ?>" class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_cv.php?id=<?= $cv['id'] ?>" class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Supprimer définitivement ce CV ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
    function previewFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            input.parentNode.querySelector('.file-name').textContent = fileName;
        }
    }
    </script>
</body>
</html>


