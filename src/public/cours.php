<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cours.php';
require_once __DIR__ . '/../classes/CoursRepository.php';

session_start();
// TEMPORAIRE : simule la connexion de Victoria (id 1 dans seed.sql).
// À remplacer par la vraie vérification quand la page de connexion existera.
$_SESSION['id_eleve'] ??= 1;
$idEleve = (int) $_SESSION['id_eleve'];

$repository = new CoursRepository($pdo);

$couleurs = [];
$cours = [];
$erreur = "";
$nom = "";          // conservés pour réafficher le formulaire en cas d'erreur
$idCouleur = 0;

try {
    $couleurs = $repository->trouverCouleurs();
    $idsValides = array_column($couleurs, 'id_couleur');

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
            $repository->creer(new Cours($nom, $idEleve, $idCouleur));
            header("Location: cours.php?ok=1");   // évite le double envoi au rechargement
            exit;
        }
    }

    $cours = $repository->trouverParEleve($idEleve);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        // Violation de UNIQUE (id_eleve, nom)
        $erreur = "Vous avez déjà un cours avec ce nom.";
        $cours = $repository->trouverParEleve($idEleve);
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
        <!-- Validation côté client : confort uniquement, la vraie validation est côté serveur -->
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
