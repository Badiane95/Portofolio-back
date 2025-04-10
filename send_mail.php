<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration des champs du formulaire
$fields = [
    [
        'field_name' => 'name',
        'label' => 'Nom',
        'field_type' => 'text',
        'placeholder' => 'Votre nom',
        'is_required' => true,
        'options' => ''
    ],
    [
        'field_name' => 'email',
        'label' => 'Email',
        'field_type' => 'email',
        'placeholder' => 'Votre email',
        'is_required' => true,
        'options' => ''
    ],
    [
        'field_name' => 'contact_type',
        'label' => 'Type de contact',
        'field_type' => 'select',
        'placeholder' => '',
        'is_required' => true,
        'options' => 'Question, Devis, Collaboration, Autre'
    ],
    [
        'field_name' => 'message',
        'label' => 'Message',
        'field_type' => 'textarea',
        'placeholder' => 'Votre message',
        'is_required' => true,
        'options' => ''
    ]
];

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Protection anti-spam honeypot
    if (!empty($_POST['website'])) {
        http_response_code(403);
        exit('Spam detected');
    }

    $requiredFields = ['name', 'email', 'contact_type', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Le champ $field est requis"]);
            exit();
        }
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Format d'email invalide"]);
        exit();
    }

    $name = test_input($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contact_type = test_input($_POST['contact_type']);
    $message = test_input($_POST['message']);

    $to = "badiane.falou95@gmail.com";
    $subject = "[$contact_type] Nouveau message de $name";

    $body = "
        <html>
        <head><style>body {font-family: Arial, sans-serif;}</style></head>
        <body>
            <h2>Nouveau message de contact</h2>
            <p><strong>Nom :</strong> $name</p>
            <p><strong>Email :</strong> $email</p>
            <p><strong>Type :</strong> " . ucfirst($contact_type) . "</p>
            <hr>
            <p><strong>Message :</strong></p>
            <p>" . nl2br($message) . "</p>
        </body>
        </html>
    ";

    $headers = "From: \"Site Web\" <noreply@badiane.falou95@gmail.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Return-Path: <noreply@badiane.falou95@gmail.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 1\r\n";

    header('Content-Type: application/json');

    try {
        if (mail($to, $subject, $body, $headers, "-fnoreply@badiane.falou95@gmail.com")) {
            echo json_encode(["status" => "success", "message" => "Message envoyé avec succès !"]);
        } else {
            throw new Exception("Erreur d'envoi du serveur");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}
?>

