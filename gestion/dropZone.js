document.addEventListener('DOMContentLoaded', function () {
  // Récupère les références des éléments DOM nécessaires
  const dropZone = document.getElementById('drop-zone');
  const fileInput = document.getElementById('image');
  const preview = document.getElementById('preview');

  // Empêche les comportements de glisser-déposer par défaut et met en évidence la zone de dépôt lors du survol
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault(); // Empêche le comportement par défaut du navigateur
    e.stopPropagation(); // Arrête la propagation de l'événement aux éléments parents
    dropZone.classList.add('bg-gray-100'); // Ajoute un repère visuel à l'utilisateur
  });

  // Supprime la mise en évidence lorsque l'élément glissé quitte la zone de dépôt
  dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('bg-gray-100');
  });

  // Gère le fichier déposé
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('bg-gray-100');

    const file = e.dataTransfer.files[0]; // Récupère le premier fichier parmi les fichiers déposés
    handleFile(file); // Traite le fichier
    
    // Associe le fichier déposé à l'input de fichier
    const dt = new DataTransfer(); // Utilise DataTransfer pour définir programmatiquement les fichiers de l'input
    dt.items.add(file); // Ajoute le fichier déposé à l'objet DataTransfer
    fileInput.files = dt.files; // Attribue le fichier à l'input de fichier
  });

  // Déclenche le clic sur l'input de fichier lorsque la zone de dépôt est cliquée
  dropZone.addEventListener('click', () => {
    fileInput.click(); // Déclenche programmatiquement le clic sur l'input caché
  });

  // Gère la sélection de fichier depuis l'input de fichier
  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0]; // Récupère le fichier sélectionné
    handleFile(file); // Traite le fichier
  });

  // Fonction pour gérer le traitement du fichier (validation, prévisualisation)
  function handleFile(file) {
    // Vérifie si un fichier a été sélectionné et si son type est autorisé
    if (file && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      const reader = new FileReader(); // Utilise FileReader pour lire les données du fichier
      reader.onload = (e) => {
        preview.src = e.target.result; // Définit la source de l'image de prévisualisation avec les données lues
        preview.classList.remove('hidden'); // Rend l'image de prévisualisation visible
      };
      reader.readAsDataURL(file); // Lit le fichier sous forme d'URL de données
    } else if (file) {
      // Affiche une alerte pour les types de fichiers non valides
      alert('Veuillez sélectionner une image valide (JPEG, PNG ou WebP).');
      fileInput.value = ''; // Réinitialise l'input pour effacer le fichier non valide
      preview.classList.add('hidden'); // Masque l'image de prévisualisation
    }
  }
});
