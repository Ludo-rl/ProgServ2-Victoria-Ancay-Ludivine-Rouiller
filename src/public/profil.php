<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';

session_start();

// Page réservée aux élèves connectés
if (empty($_SESSION["id_eleve"])) {
    header("Location: login.php");
    exit;
}
$idEleve = (int) $_SESSION["id_eleve"];

$gestionDB = new GestionDB();
$elevesManager = new ElevesManager($gestionDB->getPdo());

$erreur = "";
$identifiant = "";
$email = "";   // conservé pour réafficher le formulaire en cas d'erreur

try {
    $eleve = $elevesManager->trouverParId($idEleve);

    if ($eleve === null) {
        // Le compte n'existe plus : on déconnecte
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $identifiant = $eleve->getIdentifiant();
    $email = $eleve->getEmail() ?? "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // NETTOYAGE
        $email = trim($_POST["email"] ?? "");

        // VALIDATION côté serveur (l'e-mail est facultatif)
        if ($email !== "" && (mb_strlen($email) > Eleves::EMAIL_LONGUEUR_MAX || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $erreur = "Cette adresse e-mail n'est pas valide.";
        } else {
            $elevesManager->modifierEmail($idEleve, $email === "" ? null : $email);
            header("Location: profil.php?ok=1");   // évite le double envoi au rechargement
            exit;
        }
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Violation de UNIQUE (email)
        $erreur = "Cette adresse e-mail est déjà utilisée.";
    } else {
        error_log($e->getMessage());
        $erreur = "Une erreur est survenue. Réessayez plus tard.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Mon profil</h1>
    <p>Les informations de votre compte, simplement.</p>

    <?php if (isset($_GET["ok"])) { ?>
        <p>Vos informations ont été enregistrées.</p>
    <?php } ?>

    <?php if ($erreur !== "") { ?>
        <p style="color:#B03A2E"><?= htmlspecialchars($erreur) ?></p>
    <?php } ?>

    <h2>Informations du compte</h2>
    <form action="profil.php" method="POST">
        <label for="identifiant">Identifiant unique</label><br>
        <input type="text" id="identifiant" value="<?= htmlspecialchars($identifiant) ?>" disabled>
        <br>
        <small>Votre identifiant est utilisé pour vous connecter.</small>
        <br><br>

        <label for="email">Adresse e-mail</label> <small>facultative</small><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <br>
        <small>Cette adresse permet la récupération du mot de passe.</small>
        <br><br>

        <p>Mot de passe : défini à la création du compte. Il n'est jamais affiché.</p>

        <button type="submit">Enregistrer</button>
        <a href="cours.php">Retour à mes cours</a>
    </form>

    <p><a href="logout.php">Se déconnecter</a></p>
</body>

</html>
