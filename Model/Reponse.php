<?php

class Reponse
{
    private int $id_reponse;
    private int $id_reclamation;
    private string $description_reponse;

    public function getId_reponse(): int
    {
        return $this->id_reponse;
    }

    public function setId_reponse(int $id_reponse): void
    {
        $this->id_reponse = $id_reponse;
    }

    public function getId_reclamation(): int
    {
        return $this->id_reclamation;
    }

    public function setId_reclamation(int $id_reclamation): void
    {
        $this->id_reclamation = $id_reclamation;
    }

    public function getDescription_reponse(): string
    {
        return $this->description_reponse;
    }

    public function setDescription_reponse(string $description_reponse): void
    {
        $this->description_reponse = $description_reponse;
    }
}

?>
