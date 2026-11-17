<?php
// Une couleur de la palette.
class Couleur {
    private int $id;
    private string $nom;
    private string $codeHex;

    public function __construct(string $nom, string $codeHex, int $id) {
        $this->nom = $nom;
        $this->codeHex = $codeHex;
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getCodeHex(): string {
        return $this->codeHex;
    }
}
