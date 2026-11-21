<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';

session_start();

// Déjà connecté : on va directement sur les cours
if (!empty($_SESSION["id_eleve"])) {
    header("Location: cours.php");
    exit;
}

$gestionDB = new GestionDB();
$elevesManager = new EleveManager($gestionDB->getPdo());

$erreur = "";
$identifiant = "";   // conservé pour réafficher le formulaire en cas d'erreur

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $identifiant = trim($_POST["identifiant"] ?? "");
        $motDePasse = $_POST["mot_de_passe"] ?? "";   // jamais de trim() sur un mot de passe

        $eleve = $elevesManager->trouverParIdentifiant($identifiant);

        // password_verify compare le mot de passe saisi avec le hash de la base
        if ($eleve !== null && password_verify($motDePasse, $eleve->getMotDePasseHash())) {
            session_regenerate_id(true);   // nouvel identifiant de session après la connexion
            $_SESSION["id_eleve"] = $eleve->getId();
            header("Location: cours.php");
            exit;
        }

        // Même message dans les deux cas : on ne dit pas si c'est l'identifiant qui n'existe pas
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $erreur = "Une erreur est survenue. Réessayez plus tard.";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <p>BIENVENU.E SUR CENTRAL</p>
    <h1>Heureux.se de vous revoir.</h1>
    <p>Connectez-vous pour retrouver vos cours et reprendre votre étude.</p>

    <?php if ($erreur !== "") { ?>
        <p style="color:#B03A2E"><?= htmlspecialchars($erreur) ?></p>
    <?php } ?>

    <form action="login.php" method="POST">
        <label for="identifiant">Identifiant</label><br>
        <input type="text" id="identifiant" name="identifiant" required
            maxlength="<?= Eleves::IDENTIFIANT_LONGUEUR_MAX ?>"
            value="<?= htmlspecialchars($identifiant) ?>">
        <br><br>

        <label for="mot_de_passe">Mot de passe</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <br><br>

        <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a></p>
</body>

</html>
