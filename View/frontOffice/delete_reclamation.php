<?php

require "../../Controller/ReclamationC.php";

//var_dump($description);



$id_reclamation=$_POST["id_reclamation"];



$reclamation= new ReclamationC();
$result=$reclamation->SupprimerReclamation($id_reclamation);

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