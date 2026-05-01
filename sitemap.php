<?php
declare(strict_types=1);
require "include/functions.inc.php";
$title = "Plan du site";
$description = "Structure et navigation du site EcoPlein";
require_once "include/header.inc.php";
?>

<main>
    <h1>Plan du site</h1>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="results.php">Prix carburant</a></li>
        <li><a href="proximite.php">Stations à proximité</a></li>
        <li><a href="stats.php">Statistiques</a></li>
        <li><a href="tech.php">Page tech</a></li>
        <li><a href="sitemap.php">Plan du site</a></li>
    </ul>
</main>

<?php require_once "include/footer.inc.php"; ?>
