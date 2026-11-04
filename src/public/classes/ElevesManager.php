<?php
class ElevesManager {
    public const IDENTIFIANT_LONGUEUR_MAX = 50;
    public const MOT_DE_PASSE_LONGUEUR_MIN = 8;

    private ?int $id;               // null tant que l'élève n'est pas enregistré IA (?devant le type)
    private string $identifiant;
    private ?string $email;         // facultatif : peut être null IA
    private string $motDePasseHash;

    public function __construct(string $identifiant, ?string $email, string $motDePasseHash, ?int $id = null) {
        $this->identifiant = $identifiant;
        $this->email = $email;
        $this->motDePasseHash = $motDePasseHash;
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getIdentifiant(): string {
        return $this->identifiant;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function getMotDePasseHash(): string {
        return $this->motDePasseHash;
    }

    //simplifié nom user 
    public function getNomAffiche(): string {
        $premierePartie = explode(".", $this->identifiant)[0];
        return ucfirst($premierePartie);
    }


    public function getInitiale(): string {
        return strtoupper(substr($this->identifiant, 0, 1));
    }
}
