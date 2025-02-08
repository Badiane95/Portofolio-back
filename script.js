Swal.fire({
    title: 'Êtes-vous sûr ?',
    text: "Cette action ne peut pas être annulée !",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Oui, supprimer!'
  }).then((result) => {
    if (result.isConfirmed) {
      // Action à effectuer si confirmé
    }
  })
  