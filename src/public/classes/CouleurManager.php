<?php
// Toutes les requêtes SQL qui concernent les couleurs.
class CouleurManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Toutes les couleurs de la palette (une liste d'objets Couleur)
    public function trouverToutes(): array {
        $stmt = $this->pdo->prepare("SELECT id_couleur, nom, code_hex FROM couleur ORDER BY id_couleur");
        $stmt->execute();

        $listeCouleurs = [];
        while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // On crée un objet Couleur avec les données de la ligne
            $couleur = new Couleur(
                $ligne["nom"],
                $ligne["code_hex"],
                (int) $ligne["id_couleur"]
            );
            $listeCouleurs[] = $couleur;
        }
        return $listeCouleurs;
    }
}
