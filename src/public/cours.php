<?php
require_once __DIR__ . '/classes/Cours.php';
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/CoursManager.php';

session_start();
$_SESSION['id_eleve'] ??= 1;   // TEMPORAIRE active session en attendant vrai connexion
$idEleve = (int) $_SESSION['id_eleve'];

$gestionDB = new GestionDB();
$coursManager = new CoursManager($gestionDB->getPdo());

//$repository = $coursManager;

$couleurs = [];
$cours = [];
$erreur = "";
$nom = "";          // réafficher le formulaire en cas d'erreur
$idCouleur = 0;

try {
    $couleurs = $coursManager->trouverCouleurs();
    $idsValides = array_column($couleurs, 'id_couleur');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // NETTOYAGE 
        $nom = trim($_POST["nom"] ?? "");
        $idCouleur = (int) ($_POST["id_couleur"] ?? 0);

        // val serveur
        if ($nom === "") {
            $erreur = "Renseignez un nom pour créer votre cours.";
        } elseif (mb_strlen($nom) > Cours::NOM_LONGUEUR_MAX) {
            $erreur = "Le nom ne peut pas dépasser " . Cours::NOM_LONGUEUR_MAX . " caractères.";
        } elseif (!in_array($idCouleur, $idsValides)) {
            $erreur = "Choisissez une couleur dans la palette.";
        } else {
            // $coursManager->creer(new Cours($nom, $idEleve, $idCouleur, null));
            header("Location: cours.php?ok=1"); // IA sert a pas avoir de page blanche
            exit;
        }
    }

    $cours = $coursManager->trouverParEleve($idEleve);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        $erreur = "Vous avez déjà un cours avec ce nom.";
        $cours = $coursManager->trouverParEleve($idEleve);
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
    <title>Mes cours · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <h1>Mes cours</h1>

    <?php if (isset($_GET["ok"])) { ?>
        <p>Le cours a été créé.</p>
    <?php } ?>

    <?php if ($erreur !== "") { ?>
        <p style="color:#B03A2E"><?= htmlspecialchars($erreur) ?></p>
    <?php } ?>

    <h2>Ajouter un cours</h2>
    <form action="cours.php" method="POST">
        <label for="nom">Nom de la branche</label><br>

        <input type="text" id="nom" name="nom" required
            maxlength="<?= Cours::NOM_LONGUEUR_MAX ?>"
            value="<?= htmlspecialchars($nom) ?>">
        <br>

        <p>Couleur du cours</p>
        <?php foreach ($couleurs as $couleur) { ?>
            <label>
                <input type="radio" name="id_couleur" required
                    value="<?= (int) $couleur["id_couleur"] ?>"
                    <?= (int) $couleur["id_couleur"] === $idCouleur ? "checked" : "" ?>>
                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;
                             background:<?= htmlspecialchars($couleur["code_hex"]) ?>"></span>
                <?= htmlspecialchars($couleur["nom"]) ?>
            </label>
        <?php } ?>
        <br><br>

        <button type="submit">Créer le cours</button>
    </form>

    <h2>Vos cours</h2>
    <?php if (count($cours) === 0) { ?>
        <p>Vous n'avez pas encore de cours.</p>
    <?php } else { ?>
        <ul>
            <?php foreach ($cours as $unCours) { ?>
                <li style="border-left:8px solid <?= htmlspecialchars($unCours["code_hex"]) ?>;padding-left:8px">
                    <strong><?= htmlspecialchars($unCours["nom"]) ?></strong>
                    · <?= htmlspecialchars($unCours["couleur"]) ?>
                    · <?= (int) $unCours["nb_resumes"] ?> résumé(s)
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</body>

</html>
