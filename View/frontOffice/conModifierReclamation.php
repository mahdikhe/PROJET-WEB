<?php

require "../../Controller/ReclamationC.php";

//var_dump($description);
session_start();
$reclamation= new ReclamationC();
$id_reclamation=$_POST['id_reclamation'];
$description=$_POST['Description'];
$titre=$_POST['Titre'];
$raison=$_POST['Raison'];

$id_user=1;
$result=$reclamation->ModifierReclamation($id_reclamation,1,$description,$titre,$raison);

if($result)
{
header("Location: Reclamation.php");
exit();
}
else
{
echo "erreur";
}
?>