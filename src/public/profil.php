<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';

session_start();
require_once __DIR__ . '/langue.php';

// Page réservée aux élèves connectés
if (empty($_SESSION["id_eleve"])) {
    header("Location: login.php");
    exit;
}
$idEleve = (int) $_SESSION["id_eleve"];

$gestionDB = new GestionDB();
$elevesManager = new EleveManager($gestionDB->getPdo());

$erreur = "";
$identifiant = "";
$nomAffiche = "";
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
    $nomAffiche = $eleve->getNomAffiche();
    $email = $eleve->getEmail() ?? "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // NETTOYAGE
        $email = trim($_POST["email"] ?? "");

        // VALIDATION côté serveur (l'e-mail est facultatif)
        if ($email !== "" && (mb_strlen($email) > Eleves::EMAIL_LONGUEUR_MAX || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $erreur = t("profil.erreur_email");
        } else {
            $elevesManager->modifierEmail($idEleve, $email === "" ? null : $email);
            header("Location: profil.php?ok=1");   // évite le double envoi au rechargement
            exit;
        }
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Violation de UNIQUE (email)
        $erreur = t("profil.erreur_email_pris");
    } else {
        error_log($e->getMessage());
        $erreur = t("erreur.generale");
    }
}

// Variables lues par partials/header.php
$titrePage = t("profil.titre");
$menuActif = "profil";
$cssPage = "profil";
require __DIR__ . '/partials/header.php';
?>

<!-- Icônes propres à cette page -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
    <symbol id="i-at-sign" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="4" />
        <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8" />
    </symbol>
    <symbol id="i-mail" viewBox="0 0 24 24">
        <rect width="20" height="16" x="2" y="4" rx="2" />
        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
    </symbol>
    <symbol id="i-key" viewBox="0 0 24 24">
        <path d="m21 2-9.6 9.6" />
        <circle cx="7.5" cy="15.5" r="5.5" />
        <path d="m15.5 7.5 3 3L22 7l-3-3" />
    </symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24">
        <path d="m12 19-7-7 7-7" />
        <path d="M19 12H5" />
    </symbol>
</svg>

<header class="page-header">
    <div>
        <nav class="breadcrumb" aria-label="<?= htmlspecialchars(t("profil.fil_ariane")) ?>"><?= htmlspecialchars(t("nav.espace")) ?> / <?= htmlspecialchars(t("nav.profil")) ?></nav>
        <h1 class="page-title"><?= htmlspecialchars(t("profil.titre")) ?></h1>
        <p class="page-lead"><?= htmlspecialchars(t("profil.sous_titre")) ?></p>
    </div>
</header>

<section class="banner">
    <span class="banner__avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($nomAffiche, 0, 1))) ?></span>
    <div class="banner__body">
        <h2 class="banner__title"><?= htmlspecialchars($nomAffiche) ?></h2>
        <p class="banner__text"><?= htmlspecialchars($identifiant) ?></p>
    </div>
    <span class="pill"><svg class="icon">
            <use href="#i-user" />
        </svg><?= htmlspecialchars(t("profil.compte_eleve")) ?></span>
</section>

<div class="layout-2col">
    <section class="card">
        <h2 class="card__title"><?= htmlspecialchars(t("profil.infos_titre")) ?></h2>

        <?php if (isset($_GET["ok"])) { ?>
            <div class="alert alert--info" role="status">
                <svg class="icon">
                    <use href="#i-check" />
                </svg>
                <div><span class="alert__title"><?= htmlspecialchars(t("profil.enregistre")) ?></span></div>
            </div>
        <?php } ?>

        <?php if ($erreur !== "") { ?>
            <div class="alert alert--danger" role="alert">
                <svg class="icon">
                    <use href="#i-circle-alert" />
                </svg>
                <div><span class="alert__title"><?= htmlspecialchars($erreur) ?></span></div>
            </div>
        <?php } ?>

        <div class="info-item">
            <svg class="icon">
                <use href="#i-at-sign" />
            </svg>
            <div>
                <p class="info-item__label"><?= htmlspecialchars(t("profil.identifiant")) ?></p>
                <p class="info-item__value"><?= htmlspecialchars($identifiant) ?></p>
                <p class="info-item__help"><?= htmlspecialchars(t("profil.identifiant_aide")) ?></p>
            </div>
        </div>

        <div class="info-item">
            <svg class="icon">
                <use href="#i-mail" />
            </svg>
            <div>
                <p class="info-item__label"><?= htmlspecialchars(t("profil.email")) ?></p>
                <form class="info-form" action="profil.php" method="POST" novalidate>
                    <input class="input<?= $erreur !== "" ? " is-invalid" : "" ?>" type="email" id="email" name="email"
                        maxlength="<?= Eleves::EMAIL_LONGUEUR_MAX ?>"
                        aria-label="<?= htmlspecialchars(t("profil.email")) ?>"
                        placeholder="<?= htmlspecialchars(t("profil.email_placeholder")) ?>"
                        value="<?= htmlspecialchars($email) ?>">
                    <button class="btn btn--primary" type="submit"><?= htmlspecialchars(t("profil.enregistrer")) ?></button>
                </form>
                <p class="info-item__help"><?= htmlspecialchars(t("profil.email_aide")) ?></p>
            </div>
        </div>

        <div class="info-item">
            <svg class="icon">
                <use href="#i-key" />
            </svg>
            <div>
                <p class="info-item__label"><?= htmlspecialchars(t("profil.mot_de_passe")) ?></p>
                <p class="info-item__value"><?= htmlspecialchars(t("profil.mdp_valeur")) ?></p>
                <p class="info-item__help"><?= htmlspecialchars(t("profil.mdp_aide", Eleves::MOT_DE_PASSE_LONGUEUR_MIN)) ?></p>
            </div>
        </div>
    </section>

    <aside class="stack">
        <div class="aside-card">
            <svg class="icon">
                <use href="#i-lock" />
            </svg>
            <h2 class="aside-card__title"><?= htmlspecialchars(t("profil.perso_titre")) ?></h2>
            <p><?= htmlspecialchars(t("profil.perso_texte")) ?></p>
        </div>
        <a class="btn btn--outline btn--block" href="cours.php">
            <svg class="icon">
                <use href="#i-arrow-left" />
            </svg><?= htmlspecialchars(t("profil.retour")) ?>
        </a>
    </aside>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
