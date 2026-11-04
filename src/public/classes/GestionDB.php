<?php
require_once '../config.php';

class GestionDB {
    private PDO $pdo;

    public function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }



    // Toutes les couleurs de la palette  ----A mettre dans couleurManager apres
    public function trouverCouleurs(): array {
        $stmt = $this->pdo->prepare("SELECT id_couleur, nom, code_hex FROM couleur ORDER BY id_couleur");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
