<?php
class Cours {
    // Constante de classe : même limite que VARCHAR(100) dans la base
    public const NOM_LONGUEUR_MAX = 100;

    private string $nom;
    private int $idEleve;
    private int $idCouleur;

    public function __construct(string $nom, int $idEleve, int $idCouleur) {
        $this->nom = $nom;
        $this->idEleve = $idEleve;
        $this->idCouleur = $idCouleur;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getIdEleve(): int {
        return $this->idEleve;
    }

    public function getIdCouleur(): int {
        return $this->idCouleur;
    }
}
