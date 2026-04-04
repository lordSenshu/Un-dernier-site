<?php
    require "include/functions.inc.php";
    $title = "TD INDEX - PHP";
    $style = "/style/style.css";
    require_once "include/header.inc.php";

/* récupère les informations de l'api ghibli
   met les films dans une liste et récupère aléatoirement un des films */

    $url = "https://ghibliapi.vercel.app/films";

    $response = file_get_contents($url);
    $films = json_decode($response, true);

    $index = array_rand($films);
    $data = $films[$index];
?>

<!-- affiche le film choisi par array_rand() -->

<h1><?= $data["title"] ?></h1>

<p><strong>Réalisateur :</strong> <?= $data["director"] ?></p>
<p><strong>Année :</strong> <?= $data["release_date"] ?></p>
<p><strong>Score :</strong> <?= $data["rt_score"] ?>%</p>

<img src="<?= $data["image"] ?>" width="200" alt="Affiche du film">

<p><?= $data["description"] ?></p>

<?php
        require_once "include/footer.inc.php";
?>