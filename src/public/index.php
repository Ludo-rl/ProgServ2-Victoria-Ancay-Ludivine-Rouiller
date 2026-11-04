<?php
require_once 'classes/GestionDB.php';

$sql = "SELECT * FROM eleve";
$repository = new GestionDB();
$pdo = $repository->getPdo();

$stmt = $pdo->prepare($sql);

$stmt->execute();

$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Central</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <h1>Central</h1>

    <h2>Élèves inscrits :</h2>

    <ul>
        <?php foreach ($results as $row) { ?>
            <li>ID: <?= htmlspecialchars($row['id_eleve']) ?>
                <ul>
                    <li>Identifiant: <?= htmlspecialchars($row['identifiant']) ?></li>
                    <li>Prénom: <?= htmlspecialchars($row['prenom']) ?></li>
                    <li>E-mail: <?= htmlspecialchars($row['email'] ?? 'non renseigné') ?></li>
                </ul>
            </li>
        <?php } ?>
    </ul>
</body>

</html>
