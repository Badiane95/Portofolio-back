<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $contact_type = htmlspecialchars($_POST['contact_type']); // Nouveau champ
    $message = htmlspecialchars($_POST['message']);

    // Destinataire de l'email
    $to = "badiane.falou95@gmail.com";

    // Sujet de l'email
    $subject = "[$contact_type] Nouveau message de " . $name;

    // Message de l'email
    $body = "Nom: " . $name . "\n";
    $body .= "Email: " . $email . "\n";
    $body .= "Type de contact: " . ucfirst($contact_type) . "\n"; // Capitaliser la première lettre
    $body .= "Message:\n" . $message;

    // En-têtes de l'email
    $headers = "From: " . $email . "\r\n";

    // Envoi de l'email
    if (mail($to, $subject, $body, $headers)) {
        // Réponse JSON pour succès
        echo json_encode(["status" => "success", "message" => "Votre message a été envoyé avec succès !"]);
    } else {
        // Réponse JSON pour échec
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'envoi de votre message."]);
    }
} else {
    // Si la méthode n'est pas POST
    http_response_code(405); // Méthode non autorisée
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
}
?>
