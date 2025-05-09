<?php

require "../../Controller/ReclamationC.php";

//var_dump($description);

$reclamation= new ReclamationC();

$description=$_POST["Description"];
$titre=$_POST["Titre"];
$raison=$_POST["Raison"];
var_dump($titre);
var_dump($raison);
$id_user=1;
$result=$reclamation->ajouterReclamation(1,$description,$titre,$raison);
if($result)
{
header("Location: ../frontOffice/Reclamation.php");
exit();
}
else
{
echo "erreur";
}

?>