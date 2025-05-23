<?php
 require "ReclamationC.php";

class ReponseC{

public function listeReponse()
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("Select rec.description_reclamation,rec.date_reclamation,rec.status_reclamation,rec.titre_reclamation,raison_reclamation,rep.description_reponse,rep.id_reponse from reponse rep Join reclamation rec on rep.id_reclamation=rec.id_reclamation ");
$requette->execute();
$result=$requette->fetchAll(PDO::FETCH_ASSOC);
return $result;
} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}
}

public function ajouterReponse($id_rec, $description_reponse)
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("INSERT INTO reponse ( id_reclamation,description_reponse) VALUES (:id_rec, :description_reponse)");

        // Bind parameters with types
        $requette->bindParam(":id_rec", $id_rec, PDO::PARAM_INT);
        $requette->bindParam(":description_reponse", $description_reponse, PDO::PARAM_STR);
        
        // Execute the statement
        $result = $requette->execute();
        
        return $result; // Return the result directly
    } catch (PDOException $e) {
        // Handle the exception (logging or rethrowing)
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to add reponse. Please try again later.");
    }
}

public function ModiferReponse($id_reponse,$description_reponse)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("UPDATE   reponse SET description_reponse=:description_reponse WHERE id_reponse=:id_reponse ");
    $requette->bindParam(":description_reponse",$description_reponse);
    $requette->bindParam(":id_reponse",$id_reponse);

    $result= $requette->execute();
    return $result; // Return the result directly
} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}
}

public function SupprimerReponse($id_reponse)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("DELETE FROM   reponse WHERE id_reponse=:id_r ");
    $requette->bindParam(":id_r",$id_reponse);
    $result= $requette->execute();
    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}
}

public function getDescriptionById($id_reponse)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("SELECT description_reponse FROM   reponse WHERE id_reponse=:id_r ");
    $requette->bindParam(":id_r",$id_reponse);
      $requette->execute();
    $result=$requette->fetch(PDO::FETCH_ASSOC);

    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}
}

public function getIdReclamationByIdReponse($id_reponse)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("SELECT id_reclamation FROM   reponse WHERE id_reponse=:id_r ");
    $requette->bindParam(":id_r",$id_reponse);
      $requette->execute();
    $result=$requette->fetch(PDO::FETCH_ASSOC);

    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}
}

public function getReponseStats()
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN rec.status_reclamation = 'En attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN rec.status_reclamation = 'Résolu' THEN 1 ELSE 0 END) as resolu,
            SUM(CASE WHEN rec.status_reclamation = 'Annulé' THEN 1 ELSE 0 END) as annule
            FROM reponse rep 
            JOIN reclamation rec ON rep.id_reclamation = rec.id_reclamation");
        $requette->execute();
        $result = $requette->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to get response statistics. Please try again later.");
    }
}

public function getMonthlyStats()
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("SELECT 
            DATE_FORMAT(rec.date_reclamation, '%b') as month,
            SUM(CASE WHEN rec.status_reclamation = 'En attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN rec.status_reclamation = 'Résolu' THEN 1 ELSE 0 END) as resolu,
            SUM(CASE WHEN rec.status_reclamation = 'Annulé' THEN 1 ELSE 0 END) as annule
            FROM reponse rep 
            JOIN reclamation rec ON rep.id_reclamation = rec.id_reclamation
            GROUP BY DATE_FORMAT(rec.date_reclamation, '%Y-%m')
            ORDER BY rec.date_reclamation");
        $requette->execute();
        $result = $requette->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to get monthly statistics. Please try again later.");
    }
}

}
?>