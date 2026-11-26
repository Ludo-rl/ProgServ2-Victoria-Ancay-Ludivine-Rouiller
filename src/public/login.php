<?php
require_once __DIR__ . '/classes/GestionDB.php';
require_once __DIR__ . '/classes/Eleves.php';
require_once __DIR__ . '/classes/ElevesManager.php';

session_start();
require_once __DIR__ . '/langue.php';

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
        $erreur = t("login.erreur_identifiants");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $erreur = t("erreur.generale");
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langue) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t("login.titre_page")) ?> · Central</title>
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
            <p class="eyebrow"><?= htmlspecialchars(t("login.accroche")) ?></p>
            <h1 class="auth__title"><?= htmlspecialchars(t("login.titre")) ?></h1>
            <p class="auth__lead"><?= htmlspecialchars(t("login.intro")) ?></p>

            <form action="login.php" method="POST">
                <div class="field">
                    <label class="field__label" for="identifiant"><?= htmlspecialchars(t("login.identifiant")) ?></label>
                    <input class="input" type="text" id="identifiant" name="identifiant" required
                        maxlength="<?= Eleves::IDENTIFIANT_LONGUEUR_MAX ?>"
                        autocomplete="username"
                        value="<?= htmlspecialchars($identifiant) ?>">
                </div>

                <div class="field">
                    <label class="field__label" for="mot_de_passe"><?= htmlspecialchars(t("login.mot_de_passe")) ?></label>
                    <div class="input-wrap input-wrap--toggle">
                        <input class="input<?= $erreur !== "" ? " is-invalid" : "" ?>" type="password" id="mot_de_passe" name="mot_de_passe" required
                            autocomplete="current-password">
                        <button class="input-wrap__toggle" type="button" id="afficher-mdp"
                            aria-label="<?= htmlspecialchars(t("login.afficher_mdp")) ?>">
                            <svg class="icon">
                                <use href="#i-eye-off" />
                            </svg>
                        </button>
                    </div>
                </div>

                <?php if ($erreur !== "") { ?>
                    <div class="alert alert--danger" role="alert">
                        <svg class="icon">
                            <use href="#i-circle-alert" />
                        </svg>
                        <div><span class="alert__title"><?= htmlspecialchars($erreur) ?></span><span class="auth__detail"><?= htmlspecialchars(t("login.erreur_detail")) ?></span></div>
                    </div>
                <?php } ?>

                <button class="btn btn--primary btn--block" type="submit">
                    <svg class="icon">
                        <use href="#i-arrow-right" />
                    </svg><?= htmlspecialchars(t("login.bouton")) ?>
                </button>
            </form>

            <p class="auth__alt"><?= htmlspecialchars(t("login.pas_de_compte")) ?>
                <a href="creer-compte.php"><?= htmlspecialchars(t("login.creer_compte")) ?></a>
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
