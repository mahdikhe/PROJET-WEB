<?php
 require "config.php";

class ReclamationC{

public function listeReclamation()
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("SELECT id_reclamation, description_reclamation, date_reclamation, status_reclamation, titre_reclamation, raison_reclamation FROM reclamation ORDER BY date_reclamation DESC");
        $requette->execute();
        $result = $requette->fetchAll(PDO::FETCH_ASSOC);      
        return $result;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to get reclamations. Please try again later.");
    }
}
public function listeReclamationByUser($id_user)
{
    try {
    $conn=config::getConnexion();
    $requette = $conn->prepare("SELECT id_reclamation, description_reclamation, date_reclamation, status_reclamation, titre_reclamation, raison_reclamation FROM reclamation WHERE id_user = :id_user ORDER BY id_reclamation DESC");
    $requette->bindParam(":id_user", $id_user, PDO::PARAM_INT);  

    $requette->execute();
$result=$requette->fetchAll(PDO::FETCH_ASSOC);
return $result;
} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reclamation. Please try again later.");
}
}

public function ajouterReclamation($id_user, $description_reclamation,$titre,$raison)
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("INSERT INTO reclamation (id_user, description_reclamation, date_reclamation, status_reclamation,titre_reclamation,raison_reclamation) VALUES (:id_user, :description_reclamation, SYSDATE(), :status_reclamation,:titre_reclamation,:raison_reclamation)");

        // Bind parameters with types
        $requette->bindParam(":id_user", $id_user, PDO::PARAM_INT);
        $requette->bindParam(":description_reclamation", $description_reclamation, PDO::PARAM_STR);
        
        $stat = "En attente";
        $requette->bindParam(":status_reclamation", $stat, PDO::PARAM_STR);
        
        $requette->bindParam(":titre_reclamation", $titre, PDO::PARAM_STR);
        $requette->bindParam(":raison_reclamation", $raison, PDO::PARAM_STR);

        // Execute the statement
        $result = $requette->execute();
        
        return $result; // Return the result directly
    } catch (PDOException $e) {
        // Handle the exception (logging or rethrowing)
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to add reclamation. Please try again later.");
    }
}
public function ModifierReclamation($id_reclamation, $id_user, $description_reclamation,$titre_reclamation,$raison_reclamation)
{
    try {
        $conn = config::getConnexion();
        $sql = "UPDATE reclamation 
                SET id_user = :id_user, 
                    description_reclamation = :description_reclamation, 
                    date_reclamation = NOW(), 
                    status_reclamation = :status_reclamation ,
                    titre_reclamation=:titre_reclamation,
                    raison_reclamation=:raison_reclamation
                WHERE id_reclamation = :id";

        $requette = $conn->prepare($sql);

        $stat = "En attente";

        $requette->bindParam(":id_user", $id_user, PDO::PARAM_INT);
        $requette->bindParam(":description_reclamation", $description_reclamation, PDO::PARAM_STR); 
        $requette->bindParam(":status_reclamation", $stat, PDO::PARAM_STR); 
        $requette->bindParam(":titre_reclamation", $titre_reclamation, PDO::PARAM_STR); 
        $requette->bindParam(":raison_reclamation", $raison_reclamation, PDO::PARAM_STR); 

        $requette->bindParam(":id", $id_reclamation, PDO::PARAM_INT); 

      return $requette->execute();
       
        }
     catch (PDOException $e) {
        throw new Exception("Database error: " . $e->getMessage());
    }
}




public function SupprimerReclamation($id_reclamation)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("DELETE FROM   reclamation WHERE id_reclamation=:id_r ");
    $requette->bindParam(":id_r",$id_reclamation);
    $result= $requette->execute();
    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reclamation. Please try again later.");
}





}


public function AnnulerReclamation($id_reclamation)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("UPDATE  reclamation  SET status_reclamation=:status_rec WHERE id_reclamation=:id_r ");
    $requette->bindParam(":id_r",$id_reclamation);
    $stat="Annulé";
    $requette->bindParam(":status_rec",$stat);

    $result= $requette->execute();
    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reclamation. Please try again later.");
}
}

public function ModifierStatus($id_reclamation)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("UPDATE  reclamation  SET status_reclamation=:status_rec WHERE id_reclamation=:id_r ");
    $requette->bindParam(":id_r",$id_reclamation);
    $stat="Résolu";
    $requette->bindParam(":status_rec",$stat);

    $result= $requette->execute();
    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reclamation. Please try again later.");
}



}

public function ModifierStatusEnAttente($id_reclamation)
{
    try {
        $conn = config::getConnexion();
        $sql = "UPDATE reclamation 
                SET status_reclamation = :status_rec 
                WHERE id_reclamation = :id_r";
        
        $requette = $conn->prepare($sql);
        
        $stat = "En attente";
        $requette->bindParam(":status_rec", $stat, PDO::PARAM_STR);
        $requette->bindParam(":id_r", $id_reclamation, PDO::PARAM_INT);
        
        return $requette->execute(); 
    } catch (PDOException $e) {
        error_log("Database error (ModifierStatusEnAttente): " . $e->getMessage());
        throw new Exception("Unable to update status. Please try again later.");
    }
}




public function getDescriptionById($id_reclamation)
{
    try {
    $conn=config::getConnexion();
    $requette=$conn->prepare("SELECT description_reclamation,titre_reclamation,raison_reclamation FROM   reclamation WHERE id_reclamation=:id_r ");
    $requette->bindParam(":id_r",$id_reclamation);
      $requette->execute();
    $result=$requette->fetch(PDO::FETCH_ASSOC);

    return $result; // Return the result directly

} catch (PDOException $e) {
    // Handle the exception (logging or rethrowing)
    error_log("Database error: " . $e->getMessage());
    throw new Exception("Unable to add reponse. Please try again later.");
}


}

public function getReclamationStats()
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status_reclamation = 'En attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN status_reclamation = 'Résolu' THEN 1 ELSE 0 END) as resolu,
            SUM(CASE WHEN status_reclamation = 'Annulé' THEN 1 ELSE 0 END) as annule
            FROM reclamation");
        $requette->execute();
        $result = $requette->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Unable to get reclamation statistics. Please try again later.");
    }
}

public function getMonthlyStats()
{
    try {
        $conn = config::getConnexion();
        $requette = $conn->prepare("SELECT 
            DATE_FORMAT(date_reclamation, '%b') as month,
            SUM(CASE WHEN status_reclamation = 'En attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN status_reclamation = 'Résolu' THEN 1 ELSE 0 END) as resolu,
            SUM(CASE WHEN status_reclamation = 'Annulé' THEN 1 ELSE 0 END) as annule
            FROM reclamation
            GROUP BY DATE_FORMAT(date_reclamation, '%Y-%m')
            ORDER BY date_reclamation");
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