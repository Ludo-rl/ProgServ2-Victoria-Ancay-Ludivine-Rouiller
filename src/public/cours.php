<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Cours.php';
require_once __DIR__ . '/classes/CoursManager.php';
require_once __DIR__ . '/classes/Couleur.php';
require_once __DIR__ . '/classes/CouleurManager.php';


session_start();
require_once __DIR__ . '/langue.php';

if (empty($_SESSION["id_eleve"])) {
    header("Location: login.php");
    exit;
}
$idEleve = (int) $_SESSION["id_eleve"];

$gestionDB = new GestionDB();
$coursManager = new CoursManager($gestionDB->getPdo());
$couleurManager = new CouleurManager($gestionDB->getPdo());

$couleurs = [];
$couleursParId = [];   // pour retrouver une couleur à partir de son id
$idsValides = [];
$cours = [];
$erreur = "";
$nom = "";          // conservés pour réafficher le formulaire en cas d'erreur
$idCouleur = 0;

try {
    $couleurs = $couleurManager->trouverToutes();
    foreach ($couleurs as $couleur) {
        $idsValides[] = $couleur->getId();
        $couleursParId[$couleur->getId()] = $couleur;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // NETTOYAGE : on retire les espaces autour du nom
        $nom = trim($_POST["nom"] ?? "");
        $idCouleur = (int) ($_POST["id_couleur"] ?? 0);

        // VALIDATION côté serveur
        if ($nom === "") {
            $erreur = "Renseignez un nom pour créer votre cours.";
        } elseif (mb_strlen($nom) > Cours::NOM_LONGUEUR_MAX) {
            $erreur = "Le nom ne peut pas dépasser " . Cours::NOM_LONGUEUR_MAX . " caractères.";
        } elseif (!in_array($idCouleur, $idsValides)) {
            $erreur = "Choisissez une couleur dans la palette.";
        } else {
            $coursManager->create(new Cours($nom, $idEleve, $idCouleur, 0));
            header("Location: cours.php?ok=1");   // évite le double envoi au rechargement
            exit;
        }
    }

    $cours = $coursManager->findByEleve($idEleve);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Violation de UNIQUE (id_eleve, nom)
        $erreur = "Vous avez déjà un cours avec ce nom.";
        $cours = $coursManager->findByEleve($idEleve);
    } else {
        // On ne montre jamais le détail technique à l'utilisateur
        error_log($e->getMessage());
        $erreur = "Une erreur est survenue. Réessayez plus tard.";
    }
}
//gestion langues, a chnager de place potentiellement
if ($nom === "") {
    $erreur = t("erreur.nom_vide");
} elseif (mb_strlen($nom) > Cours::NOM_LONGUEUR_MAX) {
    $erreur = t("erreur.nom_trop_long", Cours::NOM_LONGUEUR_MAX);
} elseif (!in_array($idCouleur, $idsValides)) {
    $erreur = t("erreur.couleur");
}
// ...
$erreur = t("erreur.cours_existe");
// ...
$erreur = t("erreur.generale");


?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langue) ?>">

<h1><?= htmlspecialchars(t("cours.titre")) ?></h1>

<?php if (isset($_GET["ok"])) { ?>
    <p><?= htmlspecialchars(t("cours.cree")) ?></p>
<?php } ?>

<h2><?= htmlspecialchars(t("cours.ajouter")) ?></h2>
<label for="nom"><?= htmlspecialchars(t("cours.nom_label")) ?></label>
<p><?= htmlspecialchars(t("cours.couleur_label")) ?></p>
<button type="submit"><?= htmlspecialchars(t("cours.bouton_creer")) ?></button>

<h2><?= htmlspecialchars(t("cours.liste_titre")) ?></h2>
<?php if (count($cours) === 0) { ?>
    <p><?= htmlspecialchars(t("cours.aucun")) ?></p>
<?php } ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes cours · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>

<nav>
    <?php foreach (LANGUES_DISPONIBLES as $code => $nomLangue) { ?>
        <a href="?lang=<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($nomLangue) ?></a>
    <?php } ?>
</nav>

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
        <!-- Validation côté client : confort uniquement, la vraie validation est côté serveur -->
        <input type="text" id="nom" name="nom" required
            maxlength="<?= Cours::NOM_LONGUEUR_MAX ?>"
            value="<?= htmlspecialchars($nom) ?>">
        <br>

        <p>Couleur du cours</p>
        <?php foreach ($couleurs as $couleur) { ?>
            <label>
                <input type="radio" name="id_couleur" required
                    value="<?= $couleur->getId() ?>"
                    <?= $couleur->getId() === $idCouleur ? "checked" : "" ?>>
                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;
                             background:<?= htmlspecialchars($couleur->getCodeHex()) ?>"></span>
                <?= htmlspecialchars($couleur->getNom()) ?>
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
                <?php $couleurDuCours = $couleursParId[$unCours->getIdCouleur()]; ?>
                <li style="border-left:8px solid <?= htmlspecialchars($couleurDuCours->getCodeHex()) ?>;padding-left:8px">
                    <strong><?= htmlspecialchars($unCours->getNom()) ?></strong>
                    · <?= htmlspecialchars($couleurDuCours->getNom()) ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</body>

</html>
