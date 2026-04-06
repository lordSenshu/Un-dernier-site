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
        <section class="ghibli">
            <h1><?= htmlspecialchars($data["title"]) ?></h1>
            <h2><?= htmlspecialchars($data["original_title"]) ?></h2>

            <p><strong>Année de sortie :</strong> <?= htmlspecialchars($data["release_date"]) ?></p>

            <div class="films">
                <figure class="affiche">
                    <img src="<?= htmlspecialchars($data["image"]) ?>" alt="Affiche du film <?= htmlspecialchars($data["title"]) ?>" title="<?= htmlspecialchars($data["title"]) ?>">
                    <figcaption>Affiche du film <?= htmlspecialchars($data["title"]) ?></figcaption>
                </figure>

                <figure class="banner">
                    <img src="<?= htmlspecialchars($data["movie_banner"]) ?>" alt="Bannière du film <?= htmlspecialchars($data["title"]) ?>" title="<?= htmlspecialchars($data["title"]) ?>">
                    <figcaption>Bannière du film <?= htmlspecialchars($data["title"]) ?></figcaption>
                </figure>
            </div>

            <p class="description"><?= htmlspecialchars($data["description"]) ?></p>
        </section>

    <?php
    $user_ip = $_SERVER["REMOTE_ADDR"];

    if ($user_ip === "127.0.0.1" || $user_ip === "::1") {
        $user_ip = "193.54.115.192";
    }

    $api_url = "https://ipinfo.io/{$user_ip}/geo";
    $json_data = @file_get_contents($api_url);
    $geo_data = json_decode($json_data, true);
    ?>

    <section class="geo">
        <h2>Position géographique approximative</h2>
        <?php if ($geo_data && isset($geo_data['city'])): ?>
            <p><strong>Votre adresse IP :</strong> <?= htmlspecialchars($user_ip) ?></p>
            <p><strong>Région :</strong> <?= htmlspecialchars($geo_data['region'] ?? "Inconnue") ?></p>
            <p><strong>Pays :</strong> <?= htmlspecialchars($geo_data['country'] ?? "Inconnu") ?></p>
        <?php else: ?>
            <p style="color: red;">Impossible de déterminer votre position géographique.</p>
        <?php endif; ?>
    </section>

<?php
    } else {
        echo "<p>Données JSON invalides ou vides.</p>";
    }
}

require_once "include/footer.inc.php";
?>|