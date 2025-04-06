<?php
include __DIR__ . '/../connexion/msql.php'; // Inclut le fichier de connexion à la base de données.

// Requête SQL pour sélectionner toutes les images, triées par date d'upload la plus récente.
$query = "SELECT * FROM images ORDER BY upload_date DESC";
// Exécution de la requête SQL.
$result = $conn->query($query);
?>

<div class="container mx-auto px-4 py-8 bg-gray-100">
    <h2 class="text-3xl font-bold text-violet-700 mb-6">Uploaded Images</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="bg-white rounded-lg overflow-hidden shadow-md transition-transform transform hover:scale-105">
                <img src="<?php echo htmlspecialchars($row['filepath']); ?>"
                     alt="<?php echo htmlspecialchars($row['filename']); ?>"
                     class="w-full h-48 object-cover" loading="lazy">
                <div class="p-4">
                    <p class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($row['filename']); ?></p>
                    <p class="text-sm text-gray-500 mb-4">Uploaded: <?php echo date('d/m/Y', strtotime($row['upload_date'])); ?></p>
                    <a href="delete_image.php?id=<?php echo $row['id']; ?>"
                       class="inline-block bg-violet-500 text-white px-4 py-2 rounded hover:bg-violet-600 transition duration-300 focus:outline-none focus:ring-2 focus:ring-violet-400"
                       onclick="return confirm('Are you sure you want to delete this image?');">
                        Delete
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
