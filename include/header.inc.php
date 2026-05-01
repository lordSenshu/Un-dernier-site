<?php
$cookie_name = "theme";
$cookie_path = "/";
$theme = "jour";

if (isset($_GET["theme"])) {
    $t = $_GET["theme"];
    if ($t === "jour" || $t === "nuit") {
        $theme = $t;
        setcookie($cookie_name, $theme, time() + 30 * 24 * 3600, $cookie_path);
    } else {
        setcookie($cookie_name, "", time() - 3600, $cookie_path);
    }
} elseif (isset($_COOKIE[$cookie_name])) {
    if ($_COOKIE[$cookie_name] === "jour" || $_COOKIE[$cookie_name] === "nuit") {
        $theme = $_COOKIE[$cookie_name];
    } else {
        setcookie($cookie_name, "", time() - 3600, $cookie_path);
    }
}

$style = ($theme === "nuit") ? "/style/darkcss.css" : "/style/newcss.css";
$logo  = ($theme === "nuit") ? "images/logofull_darkmode.png" : "images/logofull.png";
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <meta name="description" content="<?= $description ?? '' ?>"/>
    <title><?= $title ?></title>
    <link rel="icon" href="images/icons/logofuel.ico"/>
    <link rel="stylesheet" href="<?= $style ?>"/>
</head>

<body>
<header>
    <h2 class="titre">
        <a href="index.php">
            <img src="<?= $logo ?>" alt="Logo du site">
        </a>
    </h2>

    <nav aria-label="Navigation principale">
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="results.php">Prix carburant</a></li>
            <li><a href="proximite.php">Près de moi</a></li>
        </ul>
    </nav>

    <div style="margin-left:auto;">
        <a href="?theme=jour">
            <img src="images/jour.png" alt="Jour" style="height:30px;">
        </a>
        <a href="?theme=nuit">
            <img src="images/nuit.png" alt="Nuit" style="height:30px;">
        </a>
    </div>
</header>
