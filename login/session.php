<?php
session_start();
include __DIR__ . '/../connexion/msql.php';

$error = null;
$username = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $query = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $result = $query->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin'] = $user['username'];
                header("Location: ../gestion/dashboard.php");
                exit;
            } else {
                $error = "Mauvais identifiant / mot de passe.";
            }
        } else {
            $error = "Mauvais identifiant / mot de passe.";
        }
        $query->close();
    }
}

$conn->close();



?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body><!-- Section Login Admin -->
<section class="main special bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <header class="major mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Connexion Admin</h2>
    </header>

    <!-- Messages de confirmation et d'erreur (initialement cachés) -->
    <div id="messageContainer" class="hidden mb-4">
        <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Succès!</strong>
            <span class="block sm:inline">Connexion réussie. Redirection...</span>
        </div>
        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Erreur!</strong>
            <span class="block sm:inline">Identifiant ou mot de passe incorrect.</span>
        </div>
    </div>

    <form method="POST" action="session.php" class="alt">
        <div class="row gtr-uniform grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
            <div class="col-12 sm:col-span-2">
                <input type="text" name="username" placeholder="Identifiant" required class="block w-full shadow-sm py-3 px-4 placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
            </div>
            <div class="col-12 sm:col-span-2">
                <input type="password" name="password" placeholder="Mot de passe" required class="block w-full shadow-sm py-3 px-4 placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md">
            </div>
            <div class="col-12 sm:col-span-2">
                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Se connecter</button>
            </div>
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const messageContainer = document.getElementById('messageContainer');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche la soumission par défaut

            // Récupérer les données du formulaire (à remplacer par ta logique d'envoi)
            const formData = new FormData(loginForm);

            // Simuler une requête AJAX (à remplacer par ta véritable requête)
            setTimeout(() => {
                const isLoginSuccessful = false; // Remplace par le résultat de ta requête

                if (isLoginSuccessful) {
                    // Afficher le message de succès
                    successMessage.classList.remove('hidden');
                    messageContainer.classList.remove('hidden');

                    // Masquer le message après quelques secondes et rediriger
                    setTimeout(() => {
                        successMessage.classList.add('hidden');
                        messageContainer.classList.add('hidden');
                        window.location.href = '/admin/dashboard.php'; // Remplace par ton URL de redirection
                    }, 3000);
                } else {
                    // Afficher le message d'erreur
                    errorMessage.classList.remove('hidden');
                    messageContainer.classList.remove('hidden');

                    // Masquer le message après quelques secondes
                    setTimeout(() => {
                        errorMessage.classList.add('hidden');
                        messageContainer.classList.add('hidden');
                    }, 3000);
                }
            }, 1000); // Simule un délai de 1 seconde pour la requête
        });
    });
</script>

<div class="container mx-auto px-4 py-4">
        <a 
            href="../index.php" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
            <i class="fas fa-home mr-2"></i> Retour à l'accueil
        </a>
    </div>
</body>
</html>
