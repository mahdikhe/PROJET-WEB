function validerDescription() {
  let description = document.getElementById("Description").value.trim();
  let titre = document.getElementById("Titre").value.trim();
  let raison = document.getElementById("Raison").value; 

  if (titre === "") {
    alert("Le titre est obligatoire");
    return false;
  }
  if (raison === "") {
    alert("La raison est obligatoire");
    return false;
  }
  if (description === "") {
    alert("La description est obligatoire");
    return false;
  }

  return true;
}
