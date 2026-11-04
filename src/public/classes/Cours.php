<?php
class Cours {
    // VARCHAR(100) DB
    public const NOM_LONGUEUR_MAX = 100;

    private string $nom;
    //private int $idCours;
    private int $idEleve;
    private int $idCouleur;

    public function __construct(string $nom, int $idCours, int $idEleve, int $idCouleur) {
        $this->nom = $nom;
        //$this->idCours = $idCours;
        $this->idEleve = $idEleve;
        $this->idCouleur = $idCouleur;
    }

    public function getNom(): string {
        return $this->nom;
    }
    /*public function getIdCours(): int {
        return $this->idCours;
    }*/

    public function getIdEleve(): int {
        return $this->idEleve;
    }

    public function getIdCouleur(): int {
        return $this->idCouleur;
    }
}
