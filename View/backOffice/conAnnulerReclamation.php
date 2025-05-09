<?php

require "../../Controller/ReclamationC.php";

//var_dump($description);

$reclamation= new ReclamationC();

$id_reclamation=$_POST["id_reclamation"];

$result=$reclamation->AnnulerReclamation($id_reclamation);
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