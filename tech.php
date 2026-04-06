<?php
require "include/functions.inc.php";
$title = "Page tech";
$style = "/style/newcss.css";
require_once "include/header.inc.php";

/* récupère les films depuis l'API Ghibli */
$url = "https://ghibliapi.vercel.app/films";
$response = @file_get_contents($url);

if ($response === false) {
    echo "<p>Impossible de récupérer les données de l'API Ghibli.</p>";
} else {
    $films = json_decode($response, true);

    if (is_array($films) && !empty($films)) {
        $index = array_rand($films);
        $data = $films[$index];
?>
        <section>
            <h1><?= htmlspecialchars($data["title"]) ?></h1>
            <h2><?= htmlspecialchars($data["original_title"]) ?></h2>

            <p><strong>Année de sortie :</strong> <?= htmlspecialchars($data["release_date"]) ?></p>

            <figure>
                <img src="<?= htmlspecialchars($data["image"]) ?>" width="250" alt="Affiche du film <?= htmlspecialchars($data["title"]) ?>" title="<?= htmlspecialchars($data["title"]) ?>">
                <figcaption>Affiche du film <?= htmlspecialchars($data["title"]) ?></figcaption>
            </figure>

            <figure>
                <img src="<?= htmlspecialchars($data["movie_banner"]) ?>" width="500" alt="Bannière du film <?= htmlspecialchars($data["title"]) ?>" title="<?= htmlspecialchars($data["title"]) ?>">
                <figcaption>Bannière du film <?= htmlspecialchars($data["title"]) ?></figcaption>
            </figure>

            <p><?= htmlspecialchars($data["description"]) ?></p>
        </section>
<?php
    } else {
        echo "<p>Données JSON invalides ou vides.</p>";
    }
}

require_once "include/footer.inc.php";
?>