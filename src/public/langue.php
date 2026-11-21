<?php
// À inclure dans chaque page, APRÈS session_start().

const LANGUES_DISPONIBLES = ["fr" => "Français", "en" => "English"];
const LANGUE_PAR_DEFAUT = "fr";

// Si l'utilisateur clique sur un lien ?lang=en, on mémorise son choix.
// On accepte seulement les codes de la liste (jamais une valeur libre).
if (isset($_GET["lang"]) && array_key_exists($_GET["lang"], LANGUES_DISPONIBLES)) {
    $_SESSION["langue"] = $_GET["lang"];
}

$langue = $_SESSION["langue"] ?? LANGUE_PAR_DEFAUT;
$textes = require __DIR__ . '/../langues/' . $langue . '.php';

// Retourne le texte correspondant à une clé.
// Si la clé n'existe pas, on affiche la clé : on repère vite un oubli.
// Les valeurs supplémentaires remplacent les %d / %s du texte.
function t(string $cle, ...$valeurs): string {
    global $textes;
    $texte = $textes[$cle] ?? $cle;
    return vsprintf($texte, $valeurs);
}
