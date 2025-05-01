function validerDescription() {
  // Get values from the form fields
  let titre = document.getElementById("Titre").value.trim();
  let raison = document.getElementById("Raison").value.trim();
  let description = document.getElementById("Description").value.trim();

  // Array to store error messages
  let errors = [];

  // Validate Titre
  if (titre === "") {
      errors.push("Le titre est obligatoire");
  }

  // Validate Raison
  if (raison === "") {
      errors.push("La raison est obligatoire");
  }

  // Validate Description
  if (description === "") {
      errors.push("La description est obligatoire");
  }

  // If there are errors, display them in a single alert
  if (errors.length > 0) {
      let errorMessage = errors.join("\n"); // Join all errors with newline
      alert(errorMessage);
      return false; // Prevent form submission
  }

  // If no errors, allow form submission
  return true;
}