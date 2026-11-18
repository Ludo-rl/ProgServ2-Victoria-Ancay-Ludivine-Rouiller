<?php
class Cours {
    // VARCHAR(100) DB
    public const NOM_LONGUEUR_MAX = 100;

    private string $nom;
    private int $idEleve;
    private int $idCouleur;
    private int $id;

    public function __construct(string $nom, int $idEleve, int $idCouleur, int $id = 0) {
        $this->nom = $nom;
        $this->idEleve = $idEleve;
        $this->idCouleur = $idCouleur;
        $this->id = $id;
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

    public function getId(): int {
        return $this->id;
    }
}
