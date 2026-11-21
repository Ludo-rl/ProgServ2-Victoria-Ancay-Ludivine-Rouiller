<?php
// Toutes les requêtes SQL qui concernent les élèves.
class EleveManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Dit si un identifiant est déjà pris (true ou false)
    public function identifiantExiste(string $identifiant): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM eleve WHERE identifiant = :identifiant");
        $stmt->bindValue(':identifiant', $identifiant);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Ajoute un élève et retourne son id
    public function creer(Eleves $eleve): int {
        $sql = "INSERT INTO eleve (identifiant, email, mot_de_passe_hash)
                VALUES (:identifiant, :email, :hash)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':identifiant', $eleve->getIdentifiant());
        $stmt->bindValue(':email', $eleve->getEmail());   // null devient NULL en base
        $stmt->bindValue(':hash', $eleve->getMotDePasseHash());
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    // Retrouve un élève par son id, sinon null
    public function trouverParId(int $id): ?Eleves {
        $stmt = $this->pdo->prepare("SELECT id_eleve, identifiant, email, mot_de_passe_hash FROM eleve WHERE id_eleve = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ligne === false) {
            return null;
        }
        return new Eleves(
            $ligne["identifiant"],
            $ligne["email"],
            $ligne["mot_de_passe_hash"],
            (int) $ligne["id_eleve"]
        );
    }

    // Retrouve un élève par son identifiant (pour la connexion), sinon null
    public function trouverParIdentifiant(string $identifiant): ?Eleves {
        $stmt = $this->pdo->prepare("SELECT id_eleve, identifiant, email, mot_de_passe_hash FROM eleve WHERE identifiant = :identifiant");
        $stmt->bindValue(':identifiant', $identifiant);
        $stmt->execute();

        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ligne === false) {
            return null;
        }
        return new Eleves(
            $ligne["identifiant"],
            $ligne["email"],
            $ligne["mot_de_passe_hash"],
            (int) $ligne["id_eleve"]
        );
    }

    // Modifie l'e-mail d'un élève (null = pas d'e-mail)
    public function modifierEmail(int $id, ?string $email): void {
        $stmt = $this->pdo->prepare("UPDATE eleve SET email = :email WHERE id_eleve = :id");
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
