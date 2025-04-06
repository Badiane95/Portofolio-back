<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification des champs requis
    $requiredFields = ['name', 'email', 'contact_type', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "error", 
                "message" => "Le champ $field est requis"
            ]);
            exit();
        }
    }

    // Récupération des données
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contact_type = htmlspecialchars($_POST['contact_type']);
    $message = htmlspecialchars($_POST['message']);

    // Configuration de l'email
    $to = "badiane.falou95@gmail.com";
    $subject = "[$contact_type] Nouveau message de $name";
    
    $body = "Nom: $name\n";
    $body .= "Email: $email\n";
    $body .= "Type: " . ucfirst($contact_type) . "\n\n";
    $body .= "Message:\n$message";

    // En-têtes email
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Envoi
    header('Content-Type: application/json');
    
    try {
        if (mail($to, $subject, $body, $headers)) {
            echo json_encode([
                "status" => "success", 
                "message" => "Message envoyé avec succès !"
            ]);
        } else {
            throw new Exception("Erreur d'envoi du serveur");
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error", 
            "message" => $e->getMessage()
        ]);
    }
    exit();
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error", 
        "message" => "Méthode non autorisée"
    ]);
    exit();
}
?>