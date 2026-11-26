<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';

session_start();
require_once __DIR__ . '/langue.php';

// Déjà connecté : pas besoin de créer un compte
if (!empty($_SESSION["id_eleve"])) {
    header("Location: cours.php");
    exit;
}

$gestionDB = new GestionDB();
$elevesManager = new EleveManager($gestionDB->getPdo());

// Un message d'erreur par champ (vide = pas d'erreur)
$erreurs = ["identifiant" => "", "mot_de_passe" => "", "email" => "", "general" => ""];
$identifiant = "";   // conservés pour réafficher le formulaire (jamais le mot de passe)
$email = "";

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $identifiant = trim($_POST["identifiant"] ?? "");
        $motDePasse = $_POST["mot_de_passe"] ?? "";   // jamais de trim() sur un mot de passe
        $email = trim($_POST["email"] ?? "");

        // 1. Identifiant : obligatoire, pas trop long, pas déjà pris
        if ($identifiant === "") {
            $erreurs["identifiant"] = t("creer.erreur_identifiant_vide");
        } elseif (mb_strlen($identifiant) > Eleves::IDENTIFIANT_LONGUEUR_MAX) {
            $erreurs["identifiant"] = t("creer.erreur_identifiant_long", Eleves::IDENTIFIANT_LONGUEUR_MAX);
        } elseif ($elevesManager->identifiantExiste($identifiant)) {
            $erreurs["identifiant"] = t("creer.erreur_identifiant_pris");
        }

        // 2. Mot de passe : 8 caractères minimum
        if (mb_strlen($motDePasse) < Eleves::MOT_DE_PASSE_LONGUEUR_MIN) {
            $erreurs["mot_de_passe"] = t("creer.erreur_mdp_court", Eleves::MOT_DE_PASSE_LONGUEUR_MIN);
        }

        // 3. E-mail : facultatif, mais correct s'il est rempli
        if ($email !== "") {
            if (mb_strlen($email) > Eleves::EMAIL_LONGUEUR_MAX || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreurs["email"] = t("creer.erreur_email");
            }
        }

        // Aucune erreur : on enregistre l'élève puis on le connecte
        if (implode("", $erreurs) === "") {
            $hash = password_hash($motDePasse, PASSWORD_DEFAULT);   // on ne stocke jamais le mot de passe en clair
            $nouvelEleve = new Eleves($identifiant, $email === "" ? null : $email, $hash);
            $idEleve = $elevesManager->creer($nouvelEleve);

            session_regenerate_id(true);
            $_SESSION["id_eleve"] = $idEleve;
            header("Location: cours.php");
            exit;
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    // 23000 = valeur déjà existante (identifiant ou e-mail pris entre-temps)
    if ($e->getCode() === "23000") {
        $erreurs["identifiant"] = t("creer.erreur_deja_pris");
    } else {
        $erreurs["general"] = t("erreur.generale");
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langue) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t("creer.titre_page")) ?> · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>
    <!-- Icônes utilisées dans la page -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="i-circle-alert" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" x2="12" y1="8" y2="12" />
            <line x1="12" x2="12.01" y1="16" y2="16" />
        </symbol>
        <symbol id="i-arrow-right" viewBox="0 0 24 24">
            <path d="M5 12h14" />
            <path d="m12 5 7 7-7 7" />
        </symbol>
        <symbol id="i-eye" viewBox="0 0 24 24">
            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
            <circle cx="12" cy="12" r="3" />
        </symbol>
        <symbol id="i-eye-off" viewBox="0 0 24 24">
            <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" />
            <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" />
            <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" />
            <path d="m2 2 20 20" />
        </symbol>
    </svg>

    <div class="auth">
        <main class="auth__panel">
            <p class="eyebrow"><?= htmlspecialchars(t("creer.accroche")) ?></p>
            <h1 class="auth__title"><?= htmlspecialchars(t("creer.titre")) ?></h1>
            <p class="auth__lead"><?= htmlspecialchars(t("creer.intro")) ?></p>

            <?php if ($erreurs["general"] !== "") { ?>
                <div class="alert alert--danger" role="alert">
                    <svg class="icon">
                        <use href="#i-circle-alert" />
                    </svg>
                    <div><span class="alert__title"><?= htmlspecialchars($erreurs["general"]) ?></span></div>
                </div>
            <?php } ?>

            <form action="creer-compte.php" method="POST" novalidate>
                <div class="field">
                    <label class="field__label" for="identifiant"><?= htmlspecialchars(t("creer.identifiant")) ?></label>
                    <input class="input<?= $erreurs["identifiant"] !== "" ? " is-invalid" : "" ?>" type="text" id="identifiant" name="identifiant" required
                        maxlength="<?= Eleves::IDENTIFIANT_LONGUEUR_MAX ?>"
                        autocomplete="username"
                        value="<?= htmlspecialchars($identifiant) ?>">
                    <?php if ($erreurs["identifiant"] !== "") { ?>
                        <span class="field__error"><?= htmlspecialchars($erreurs["identifiant"]) ?></span>
                    <?php } ?>
                </div>

                <div class="field">
                    <label class="field__label" for="mot_de_passe"><?= htmlspecialchars(t("creer.mot_de_passe")) ?></label>
                    <div class="input-wrap input-wrap--toggle">
                        <input class="input<?= $erreurs["mot_de_passe"] !== "" ? " is-invalid" : "" ?>" type="password" id="mot_de_passe" name="mot_de_passe" required
                            autocomplete="new-password">
                        <button class="input-wrap__toggle" type="button" id="afficher-mdp"
                            aria-label="<?= htmlspecialchars(t("login.afficher_mdp")) ?>">
                            <svg class="icon">
                                <use href="#i-eye-off" />
                            </svg>
                        </button>
                    </div>
                    <?php if ($erreurs["mot_de_passe"] !== "") { ?>
                        <span class="field__error"><?= htmlspecialchars($erreurs["mot_de_passe"]) ?></span>
                    <?php } else { ?>
                        <span class="field__help"><?= htmlspecialchars(t("creer.mdp_aide", Eleves::MOT_DE_PASSE_LONGUEUR_MIN)) ?></span>
                    <?php } ?>
                </div>

                <div class="field">
                    <label class="field__label" for="email"><?= htmlspecialchars(t("creer.email")) ?>
                        <span class="field__optional"><?= htmlspecialchars(t("creer.facultatif")) ?></span>
                    </label>
                    <input class="input<?= $erreurs["email"] !== "" ? " is-invalid" : "" ?>" type="email" id="email" name="email"
                        maxlength="<?= Eleves::EMAIL_LONGUEUR_MAX ?>"
                        autocomplete="email"
                        placeholder="<?= htmlspecialchars(t("creer.email_placeholder")) ?>"
                        value="<?= htmlspecialchars($email) ?>">
                    <?php if ($erreurs["email"] !== "") { ?>
                        <span class="field__error"><?= htmlspecialchars($erreurs["email"]) ?></span>
                    <?php } else { ?>
                        <span class="field__help"><?= htmlspecialchars(t("creer.email_aide")) ?></span>
                    <?php } ?>
                </div>

                <button class="btn btn--primary btn--block" type="submit">
                    <svg class="icon">
                        <use href="#i-arrow-right" />
                    </svg><?= htmlspecialchars(t("creer.bouton")) ?>
                </button>
            </form>

            <p class="auth__alt"><?= htmlspecialchars(t("creer.deja_un_compte")) ?>
                <a href="login.php"><?= htmlspecialchars(t("creer.se_connecter")) ?></a>
            </p>

            <p class="auth__note">
                <?php foreach (LANGUES_DISPONIBLES as $code => $nomLangue) { ?>
                    <a href="?lang=<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($nomLangue) ?></a>
                <?php } ?>
            </p>
        </main>
    </div>

    <script>
        // Bouton « œil » : affiche ou masque le mot de passe
        const champ = document.getElementById("mot_de_passe");
        const bouton = document.getElementById("afficher-mdp");
        bouton.addEventListener("click", function() {
            const visible = champ.type === "text";
            champ.type = visible ? "password" : "text";
            bouton.querySelector("use").setAttribute("href", visible ? "#i-eye-off" : "#i-eye");
        });
    </script>
</body>

</html>
