<?php
class Eleves {
    public const IDENTIFIANT_LONGUEUR_MAX = 50;
    public const MOT_DE_PASSE_LONGUEUR_MIN = 8;

    private string $identifiant;
    private ?string $email;
    private string $motDePasseHash;

    public function __construct(string $identifiant, ?string $email, string $motDePasseHash) {
        $this->identifiant = $identifiant;
        $this->email = $email;
        $this->motDePasseHash = $motDePasseHash;
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
}
