<!DOCTYPE HTML>
<html>
<head>
    <title>Contact - Portfolio Badiane</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
      <link rel="stylesheet" href="assets/css/main.css" />
<link rel="stylesheet" href="assets/css/contact.css">

    
</head>
<body>
<h1 class="page-title">Contactez-moi</h1>

    <section id="contact">
        <div class="container">
            <form id="contactForm">
                <input type="text" name="name" placeholder="Votre nom" required>
                <input type="email" name="email" placeholder="Votre email" required>
                <select name="contact_type" required>
                    <option value="">Type de contact</option>
                    <option value="stage">Stage</option>
                    <option value="alternance">Alternance</option>
                </select>
                <textarea name="message" placeholder="Votre message" required></textarea>
                <button type="submit" class="send-button">Envoyer</button>
            </form>
        </div>
    </section>

    <script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitButton = form.querySelector('button');
        
        // Animation du bouton
        submitButton.style.animation = 'sendAnimation 0.5s';
        submitButton.disabled = true;

        // Simulation d'envoi (à remplacer par votre logique PHP/AJAX)
        fetch('send_mail.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Message envoyé !',
                    text: 'Votre message a été transmis avec succès.',
                    confirmButtonText: 'OK'
                });
                form.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de l\'envoi.'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Erreur de réseau',
                text: 'Vérifiez votre connexion internet.'
            });
            console.error('Erreur:', error);
        })
        .finally(() => {
            submitButton.style.animation = '';
            submitButton.disabled = false;
        });
    });
    </script>
    <style>
    @keyframes buttonPulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
    }

    .send-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .send-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: all 0.5s;
    }

    .send-button:hover::before {
        left: 100%;
    }

    .send-button.sending {
        animation: buttonPulse 1s infinite;
        cursor: not-allowed;
    }
</style>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button');
    submitButton.classList.add('sending');
    submitButton.disabled = true;

    // Votre logique d'envoi ici
});
</script>

<a href="index.php" class="return-button">
    <i class="fas fa-arrow-left"></i> Retour
</a>

<?php
include 'index_footer.php'; ?>



	
		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
</body>
</html>
