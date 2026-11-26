<?php
// En-tête commun à toutes les pages : <head>, icônes et barre latérale.
//
// Chaque page doit définir ces variables AVANT de faire require de ce fichier :
//   $titrePage   : titre de l'onglet (ex. "Mes cours")
//   $menuActif   : "cours" ou "profil" (le lien du menu qui est en surbrillance)
//   $cssPage     : nom du CSS de la page, sans .css (ex. "cours")
//   $nomAffiche  : prénom affiché en bas de la barre latérale
//   $identifiant : identifiant affiché sous le prénom
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langue ?? "fr") ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titrePage ?? "Central") ?> · Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/<?= htmlspecialchars($cssPage) ?>.css">
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
        <symbol id="i-user" viewBox="0 0 24 24">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
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
        <symbol id="i-log-out" viewBox="0 0 24 24">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
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
                <a class="nav__item" href="cours.php" <?= $menuActif === "cours" ? 'aria-current="page"' : "" ?>>
                    <svg class="icon">
                        <use href="#i-grid" />
                    </svg>Mes cours
                </a>
                <a class="nav__item" href="profil.php" <?= $menuActif === "profil" ? 'aria-current="page"' : "" ?>>
                    <svg class="icon">
                        <use href="#i-user" />
                    </svg>Profil
                </a>
                <a class="nav__item" href="logout.php">
                    <svg class="icon">
                        <use href="#i-log-out" />
                    </svg>Se déconnecter
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
                    <span class="avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($nomAffiche, 0, 1))) ?></span>
                    <div>
                        <div class="user__name"><?= htmlspecialchars($nomAffiche) ?></div>
                        <div class="user__email"><?= htmlspecialchars($identifiant) ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- CONTENU : se termine dans footer.php -->
        <main class="main">
            <div class="main__content">
