<?php

require "../../Controller/ReponseC.php";

//var_dump($description);

$rep= new ReponseC();

$description=$_POST["Description"];
$id_reclamation=$_POST["id_reclamation"];
$result=$rep->ajouterReponse($id_reclamation,$description);

$reclamation= new ReclamationC();
$reclamation->ModifierStatus($id_reclamation);

if($result)
{
header("Location: TableReclamation.php");
exit();
}
else
{
echo "erreur";
}

?>