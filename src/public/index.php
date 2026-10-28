<?php
const DB_HOST = 'be9lj1.myd.infomaniak.com';
const DB_PORT = 3306;
const DB_NAME = 'be9lj1_centralMDB';
const DB_USER = 'be9lj1_VicLudo';
const DB_PASSWORD = 'Victoria_Ludivine26';

$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";

$pdo = new PDO($dsn, DB_USER, DB_PASSWORD);


$sql = "SELECT * FROM eleve";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProgServ2 Course PHP Template</title>
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
