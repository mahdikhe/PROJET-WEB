<?php

require "../../Controller/ReponseC.php";

//var_dump($description);

$rep= new ReponseC();

$description=$_POST["Description"];
$id_reponse=$_POST["id_reponse"];
$result=$rep->ModiferReponse($id_reponse,$description);


if($result)
{
header("Location: TableReponse.php");
exit();
}
else
{
echo "erreur";
}

?>