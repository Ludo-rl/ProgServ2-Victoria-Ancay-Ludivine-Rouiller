<?php

class CoursManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function trouverCouleurs(): array {
        $stmt = $this->pdo->prepare("SELECT id_couleur, nom, code_hex FROM couleur ORDER BY id_couleur");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Les cours d'un élève (une liste d'objets Cours)
    public function trouverParEleve(int $idEleve): array {
        $sql = "SELECT c.id_cours, c.nom, c.id_eleve, c.id_couleur,
                       co.nom AS couleur, co.code_hex,
                       (SELECT COUNT(*) FROM resume r WHERE r.id_cours = c.id_cours) AS nb_resumes
                FROM cours c
                JOIN couleur co ON co.id_couleur = c.id_couleur
                WHERE c.id_eleve = :id_eleve
                ORDER BY c.nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_eleve', $idEleve);
        $stmt->execute();

        $listeCours = [];
        while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // On crée un objet Cours avec les données de la ligne
            $cours = new Cours(
                $ligne["nom"],
                (int) $ligne["id_eleve"],
                (int) $ligne["id_couleur"],
                (int) $ligne["id_cours"]
            );
            $listeCours[] = $cours;
        }
        return $listeCours;
    }



    // Un seul cours, seulement a un eleve sinon false
    public function trouverParId(int $idCours, int $idEleve): array|false {
        $sql = "SELECT c.id_cours, c.nom, c.id_couleur,
                       (SELECT COUNT(*) FROM resume r WHERE r.id_cours = c.id_cours) AS nb_resumes
                FROM cours c
                WHERE c.id_cours = :id_cours AND c.id_eleve = :id_eleve";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_cours', $idCours);
        $stmt->bindValue(':id_eleve', $idEleve);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Modifie le nom et la couleur (le cours doit avoir un id) IA 
    public function modifier(Cours $cours): void {
        $sql = "UPDATE cours SET nom = :nom, id_couleur = :id_couleur
                WHERE id_cours = :id_cours AND id_eleve = :id_eleve";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nom', $cours->getNom());
        $stmt->bindValue(':id_couleur', $cours->getIdCouleur());
        //$stmt->bindValue(':id_cours', $cours->getIdCours());
        $stmt->bindValue(':id_eleve', $cours->getIdEleve());
        $stmt->execute();
    }

    public function supprimer(int $idCours, int $idEleve): void {
        $stmt = $this->pdo->prepare("DELETE FROM cours WHERE id_cours = :id_cours AND id_eleve = :id_eleve");
        $stmt->bindValue(':id_cours', $idCours);
        $stmt->bindValue(':id_eleve', $idEleve);
        $stmt->execute();
    }
}
