<?php
class Reclamation
{
    private int $id_reclamation;
    private int $id_user;
    private string $description_reclamation;
    private DateTime $date_reclamation;
    private string $status_reclamation;
    private string $titre_reclamation;
    private string $raison_reclamation;

    public function getId_reclamation(): int
    {
        return $this->id_reclamation;
    }

    public function setId_reclamation(int $id): void
    {
        $this->id_reclamation = $id;
    }

    public function getId_user(): int
    {
        return $this->id_user;
    }

    public function setId_user(int $id_user): void
    {
        $this->id_user = $id_user;
    }

    public function getDescription_reclamation(): string
    {
        return $this->description_reclamation;
    }

    public function setDescription_reclamation(string $description): void
    {
        $this->description_reclamation = $description;
    }

    public function getDate_reclamation(): DateTime
    {
        return $this->date_reclamation;
    }

    public function setDate_reclamation(DateTime $date): void
    {
        $this->date_reclamation = $date;
    }

    public function getStatus_reclamation(): string
    {
        return $this->status_reclamation;
    }

    public function setStatus_reclamation(string $status): void
    {
        $this->status_reclamation = $status;
    }

    
    public function getTitre_reclamation(): string
    {
        return $this->titre_reclamation;
    }

    public function setTitre_reclamation(string $titre): void
    {
        $this->titre_reclamation = $titre;
    }

    
    public function getRaison_reclamation(): string
    {
        return $this->raison_reclamation;
    }

    public function setRaison_reclamation(string $raison): void
    {
        $this->raison_reclamation = $raison;
    }
}
?>
