<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Cours.php';
require_once __DIR__ . '/classes/CoursManager.php';
require_once __DIR__ . '/classes/Couleur.php';
require_once __DIR__ . '/classes/CouleurManager.php';

session_start();
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
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes cours · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/cours.css">
</head>

<body>
    <!-- Icônes utilisées dans la page -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="i-book-open" viewBox="0 0 24 24">
            <path d="M12 7v14" />
            <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z" />
        </symbol>
        <symbol id="i-grid" viewBox="0 0 24 24">
            <rect width="7" height="7" x="3" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="14" rx="1" />
            <rect width="7" height="7" x="3" y="14" rx="1" />
        </symbol>
        <symbol id="i-lock" viewBox="0 0 24 24">
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </symbol>
        <symbol id="i-plus" viewBox="0 0 24 24">
            <path d="M5 12h14" />
            <path d="M12 5v14" />
        </symbol>
        <symbol id="i-circle-alert" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" x2="12" y1="8" y2="12" />
            <line x1="12" x2="12.01" y1="16" y2="16" />
        </symbol>
        <symbol id="i-check" viewBox="0 0 24 24">
            <path d="M20 6 9 17l-5-5" />
        </symbol>
    </svg>

    <div class="app">

        <!-- BARRE LATÉRALE -->
        <aside class="sidebar">
            <a class="brand" href="cours.php">
                <span class="brand__logo"><svg class="icon">
                        <use href="#i-book-open" />
                    </svg></span>Central
            </a>
            <p class="sidebar__label">Mon espace</p>
            <nav class="nav" aria-label="Navigation principale">
                <a class="nav__item" href="cours.php" aria-current="page">
                    <svg class="icon">
                        <use href="#i-grid" />
                    </svg>Mes cours
                </a>
            </nav>
            <div class="sidebar__footer">
                <div class="privacy-note">
                    <svg class="icon">
                        <use href="#i-lock" />
                    </svg>
                    <strong>Votre espace personnel</strong>
                    <p>Seuls vos cours et résumés apparaissent ici.</p>
                </div>
                <div class="user">
                    <span class="avatar">V</span>
                    <div>
                        <div class="user__name">Victoria</div>
                        <div class="user__email">victoria.ancay</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- CONTENU -->
        <main class="main">
            <div class="main__content">

                <header class="page-header">
                    <div>
                        <nav class="breadcrumb" aria-label="Fil d'Ariane">Mon espace / Mes cours</nav>
                        <h1 class="page-title">Mes cours</h1>
                        <p class="page-lead">Retrouvez vos cours et ajoutez-en un nouveau.</p>
                    </div>
                </header>

                <div class="stack">

                    <?php if (isset($_GET["ok"])) { ?>
                        <div class="alert alert--info" role="status">
                            <svg class="icon">
                                <use href="#i-check" />
                            </svg>
                            <div><span class="alert__title">Le cours a été créé.</span></div>
                        </div>
                    <?php } ?>

                    <!-- Liste des cours -->
                    <section class="card">
                        <h2 class="card__title">Vos cours</h2>
                        <?php if (count($cours) === 0) { ?>
                            <p class="card__subtitle">Vous n'avez pas encore de cours.</p>
                        <?php } else { ?>
                            <p class="card__subtitle"><?= count($cours) ?> cours dans votre espace.</p>
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
                            </svg>Ajouter un cours</h2>

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
                                <label class="field__label" for="nom">Nom de la branche</label>
                                <!-- Validation côté client : confort uniquement, la vraie validation est côté serveur -->
                                <input class="input" type="text" id="nom" name="nom" required
                                    maxlength="<?= Cours::NOM_LONGUEUR_MAX ?>"
                                    placeholder="Ex. Sciences"
                                    value="<?= htmlspecialchars($nom) ?>">
                            </div>

                            <div class="field">
                                <span class="field__label">Couleur du cours</span>
                                <div class="swatches" role="radiogroup" aria-label="Couleur du cours">
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

                            <button class="btn btn--primary" type="submit">Créer le cours</button>
                        </form>
                    </section>

                </div>
            </div>

            <footer class="app-footer">
                <span>Victoria Ançay · Ludivine Rouiller</span>
                <span>Projet réalisé dans le cadre de ProgServ2 · HEIG-VD</span>
            </footer>
        </main>
    </div>
</body>

</html>
