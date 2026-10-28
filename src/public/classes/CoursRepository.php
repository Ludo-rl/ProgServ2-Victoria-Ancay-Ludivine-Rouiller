<?php
class CoursRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Palette de couleurs
    public function trouverCouleurs(): array {
        $stmt = $this->pdo->prepare("SELECT id_couleur, nom, code_hex FROM couleur ORDER BY id_couleur");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cours d'un élève, avec leur couleur et leur nombre de résumés
    public function trouverParEleve(int $idEleve): array {
        $sql = "SELECT c.id_cours, c.nom, co.nom AS couleur, co.code_hex,
                       (SELECT COUNT(*) FROM resume r WHERE r.id_cours = c.id_cours) AS nb_resumes
                FROM cours c
                JOIN couleur co ON co.id_couleur = c.id_couleur
                WHERE c.id_eleve = :id_eleve
                ORDER BY c.nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_eleve', $idEleve, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Création d'un cours (requête préparée : pas d'injection SQL possible)
    public function creer(Cours $cours): void {
        $sql = "INSERT INTO cours (nom, id_eleve, id_couleur)
                VALUES (:nom, :id_eleve, :id_couleur)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nom', $cours->getNom());
        $stmt->bindValue(':id_eleve', $cours->getIdEleve(), PDO::PARAM_INT);
        $stmt->bindValue(':id_couleur', $cours->getIdCouleur(), PDO::PARAM_INT);
        $stmt->execute();
    }
}
