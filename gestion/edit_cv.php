<?php
session_start();
require __DIR__ . '/../connexion/msql.php';

if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: ../login/session.php");
    exit;
}

$cv_id = $_GET['id'] ?? null;

if (!$cv_id) {
    $_SESSION['error'] = "ID de CV non spécifié";
    header("Location: dashboard.php");
    exit;
}

// Fetch existing CV data
$query = "SELECT * FROM cv WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cv_id);
$stmt->execute();
$cv = $stmt->get_result()->fetch_assoc();

if (!$cv) {
    $_SESSION['error'] = "CV non trouvé";
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Récupération des données
        $nom_cv = htmlspecialchars($_POST['nom_cv']);
        $description = htmlspecialchars($_POST['description']);
        $actif = isset($_POST['actif']) ? 1 : 0;

        // Update the CV data
        $stmt = $conn->prepare("UPDATE cv SET nom_cv = ?, description = ?, actif = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nom_cv, $description, $actif, $cv_id);

        if (!$stmt->execute()) {
            throw new Exception("Erreur base de données: " . $stmt->error);
        }

        $_SESSION['message'] = "CV mis à jour avec succès";
        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        $conn->close();
    }

    header("Location: dashboard.php");
    exit;
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Cv</title>
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'navback.php'; ?>
    
  <!-- Section Édition des CV -->
<section class="mb-8">
    <div class="bg-white rounded-lg shadow-md">
        <!-- Entête -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800">Édition du CV</h2>
        </div>

        <!-- Formulaire d'édition -->
        <div class="p-6">
            <h3 class="text-lg font-semibold text-purple-600 mb-4">Modifier le CV</h3>

            <?php if(isset($_SESSION['message'])): ?>
                <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Colonne gauche -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du CV *</label>
                            <input type="text" name="nom_cv" required
                                   class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   value="<?= htmlspecialchars($cv['nom_cv']) ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fichier CV *</label>
                            <div class="relative">
                                <input type="file" name="fileToUpload"
                                       accept=".pdf,.docx"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <span class="text-xs text-gray-400 mt-1 block">Formats acceptés : PDF, DOCX (max 5MB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="4"
                                      class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($cv['description']) ?></textarea>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="actif" id="actif"
                                   class="h-4 w-4 text-purple-600 rounded focus:ring-purple-500" <?= $cv['actif'] ? 'checked' : '' ?>>
                            <label for="actif" class="text-sm text-gray-700">CV visible publiquement</label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-all">
                        <i class="fas fa-save mr-2"></i>
                        Mettre à jour le CV
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
  
</body>
</html>
