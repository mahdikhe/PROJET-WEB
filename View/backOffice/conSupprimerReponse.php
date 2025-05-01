<?php

require "../../Controller/ReponseC.php";

// Check if POST data exists
if (!isset($_POST["id_reponse"])) {
    echo "ID de réponse manquant.";
    exit();
}

$id_reponse = $_POST["id_reponse"];

$rep = new ReponseC();
$reclamation = new ReclamationC();

try {
    $id_reclamation = $rep->getIdReclamationByIdReponse($id_reponse);
    $result1 = $reclamation->ModifierStatusEnAttente($id_reclamation);
    $result = $rep->SupprimerReponse($id_reponse);

    if ($result && $result1) {
        //Success - Redirect or return confirmation
      header("Location: TableReponse.php");
        exit();
    } else {
        echo "Erreur lors de la suppression ou de la mise à jour.";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
