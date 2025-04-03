document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('image');
    const preview = document.getElementById('preview');
  
    dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropZone.classList.add('bg-gray-100');
    });
  
    dropZone.addEventListener('dragleave', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropZone.classList.remove('bg-gray-100');
    });
  
    dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropZone.classList.remove('bg-gray-100');
  
      const file = e.dataTransfer.files[0];
      handleFile(file);
      
      // Associer le fichier déposé à l'input file
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
    });
  
    dropZone.addEventListener('click', () => {
      fileInput.click();
    });
  
    fileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      handleFile(file);
    });
  
    function handleFile(file) {
      if (file && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        const reader = new FileReader();
        reader.onload = (e) => {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
      } else if (file) {
        alert('Veuillez sélectionner une image valide (JPEG, PNG ou WebP).');
        fileInput.value = ''; // Réinitialise le champ si le fichier n'est pas valide
        preview.classList.add('hidden');
      }
    }
  });
  

  