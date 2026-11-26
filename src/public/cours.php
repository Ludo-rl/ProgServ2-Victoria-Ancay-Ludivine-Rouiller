<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Cours.php';
require_once __DIR__ . '/classes/CoursManager.php';
require_once __DIR__ . '/classes/Couleur.php';
require_once __DIR__ . '/classes/CouleurManager.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';


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
            $erreur = t("erreur.nom_vide");
        } elseif (mb_strlen($nom) > Cours::NOM_LONGUEUR_MAX) {
            $erreur = t("erreur.nom_trop_long", Cours::NOM_LONGUEUR_MAX);
        } elseif (!in_array($idCouleur, $idsValides)) {
            $erreur = t("erreur.couleur");
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
        $erreur = t("erreur.cours_existe");
        $cours = $coursManager->findByEleve($idEleve);
    } else {
        // On ne montre jamais le détail technique à l'utilisateur
        error_log($e->getMessage());
        $erreur = t("erreur.generale");
    }
}
$titrePage = t("cours.titre");
$menuActif = "cours";
$cssPage = "cours";
// Élève connecté : son nom et son identifiant s'affichent dans la barre latérale
$eleveConnecte = (new EleveManager($gestionDB->getPdo()))->trouverParId($idEleve);
if ($eleveConnecte === null) {
    // Le compte n'existe plus : on déconnecte
    session_destroy();
    header("Location: login.php");
    exit;
}
$identifiant = $eleveConnecte->getIdentifiant();
$nomAffiche = $eleveConnecte->getNomAffiche();
require __DIR__ . '/partials/header.php';
?>

<header class="page-header">
    <div>
        <nav class="breadcrumb" aria-label="Fil d'Ariane"><?= htmlspecialchars(t("nav.espace")) ?> / <?= htmlspecialchars(t("cours.titre")) ?></nav>
        <h1 class="page-title"><?= htmlspecialchars(t("cours.titre")) ?></h1>
        <p class="page-lead"><?= htmlspecialchars(t("cours.sous_titre")) ?></p>
    </div>
</header>

<div class="stack">

    <?php if (isset($_GET["ok"])) { ?>
        <div class="alert alert--info" role="status">
            <svg class="icon">
                <use href="#i-check" />
            </svg>
            <div><span class="alert__title"><?= htmlspecialchars(t("cours.cree")) ?></span></div>
        </div>
    <?php } ?>

    <!-- Liste des cours -->
    <section class="card">
        <h2 class="card__title"><?= htmlspecialchars(t("cours.liste_titre")) ?></h2>
        <?php if (count($cours) === 0) { ?>
            <p class="card__subtitle"><?= htmlspecialchars(t("cours.aucun")) ?></p>
        <?php } else { ?>
            <p class="card__subtitle"><?= htmlspecialchars(t("cours.nombre", count($cours))) ?></p>
            <div class="course-grid">
                <?php foreach ($cours as $unCours) { ?>
                    <?php $couleurDuCours = $couleursParId[$unCours->getIdCouleur()]; ?>
                    <div class="course-card" style="--course-color: <?= htmlspecialchars($couleurDuCours->getCodeHex()) ?>">
                        <span class="course-card__name"><?= htmlspecialchars($unCours->getNom()) ?></span>
                        <span class="course-card__tile"></span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

    <!-- Formulaire d'ajout -->
    <section class="card card--form">
        <h2 class="card__heading"><svg class="icon">
                <use href="#i-plus" />
            </svg><?= htmlspecialchars(t("cours.ajouter")) ?></h2>

        <?php if ($erreur !== "") { ?>
            <div class="alert alert--danger" role="alert">
                <svg class="icon">
                    <use href="#i-circle-alert" />
                </svg>
                <div><span class="alert__title"><?= htmlspecialchars($erreur) ?></span></div>
            </div>
        <?php } ?>

        <form action="cours.php" method="POST">
            <div class="field">
                <label class="field__label" for="nom"><?= htmlspecialchars(t("cours.nom_label")) ?></label>
                <!-- Validation côté client : confort uniquement, la vraie validation est côté serveur -->
                <input class="input" type="text" id="nom" name="nom" required
                    maxlength="<?= Cours::NOM_LONGUEUR_MAX ?>"
                    placeholder="<?= htmlspecialchars(t("cours.exemple")) ?>"
                    value="<?= htmlspecialchars($nom) ?>">
            </div>

            <div class="field">
                <span class="field__label"><?= htmlspecialchars(t("cours.couleur_label")) ?></span>
                <div class="swatches" role="radiogroup" aria-label="<?= htmlspecialchars(t("cours.couleur_label")) ?>">
                    <?php foreach ($couleurs as $couleur) { ?>
                        <div class="swatch-option">
                            <input class="visually-hidden" type="radio" name="id_couleur" required
                                id="couleur-<?= $couleur->getId() ?>"
                                value="<?= $couleur->getId() ?>"
                                <?= $couleur->getId() === $idCouleur ? "checked" : "" ?>>
                            <label class="swatch" for="couleur-<?= $couleur->getId() ?>"
                                style="--swatch: <?= htmlspecialchars($couleur->getCodeHex()) ?>"
                                title="<?= htmlspecialchars($couleur->getNom()) ?>">
                                <svg class="icon">
                                    <use href="#i-check" />
                                </svg>
                                <span class="visually-hidden"><?= htmlspecialchars($couleur->getNom()) ?></span>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <button class="btn btn--primary" type="submit"><?= htmlspecialchars(t("cours.bouton_creer")) ?></button>
        </form>
    </section>

</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
