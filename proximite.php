<?php
declare(strict_types=1);
 
require "include/functions.inc.php";
$title = "Stations à proximité";
$description = "Stations-service proches de votre position géographique actuelle";
require_once "include/header.inc.php";
 
$geo = getGeoFromIP();
$geo_ok = !empty($geo) && isset($geo['postal'], $geo['city']);
$carburant = isset($_GET['carburant']) ? htmlspecialchars($_GET['carburant']) : '';
 
$code_dep = '';
$geo_ville = '';
$geo_coord = '';
$stations = [];
$erreur = '';
 
if ($geo_ok) {
    $code_dep = codeDepDepuisPostal($geo['postal']);
    $geo_ville = $geo['city'] ?? '';
    $geo_coord = $geo['loc'] ?? '';
 
    if ($code_dep !== '') {
        $stations = getStationsParDepXML($code_dep, 50, $carburant);
        if (empty($stations)) {
            $erreur = "Aucune station trouvée pour le département <strong>$code_dep</strong> via le flux XML.";
        }
    } else {
        $erreur = "Impossible de déterminer le département depuis le code postal retourné.";
    }
} else {
    $erreur = "La géolocalisation par IP n'a pas pu être effectuée.";
}
 
// on affiche max 15 stations
$max_stations = 15;
?>
 
<main>
<h2>Stations à proximité</h2>
 
<section class="geo-info">
    <?php if ($geo_ok) : ?>
        <p>
            Position trouvée : <strong><?= htmlspecialchars($geo_ville) ?></strong>
            (département <strong><?= htmlspecialchars($code_dep) ?></strong>)
            <?php if ($geo_coord !== '') : ?>
                <br>coordonnées approximatives : <?= htmlspecialchars($geo_coord) ?>
            <?php endif ?>
        </p>
        <p class="notice"><em>La localisation par IP est approximative.</em></p>
    <?php endif ?>
</section>
 
<?php if (!empty($stations)) : ?>
 
<section class="filtre">
    <form method="get" action="proximite.php">
        <label for="carburant">Filtrer par carburant :</label>
        <select name="carburant" id="carburant">
            <option value="">Tous</option>
            <?php
            $types_carburant = ['SP95', 'SP98', 'Gazole', 'E10', 'GPL', 'E85'];
            foreach ($types_carburant as $c) :
                $sel = ($carburant === $c) ? 'selected' : '';
            ?>
                <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
            <?php endforeach ?>
        </select>
        <input type="submit" value="Filtrer">
    </form>
</section>
 
<section class="resultats">
    <?php
        $nb_affichees = min(count($stations), $max_stations);
        $nb_total = count($stations);
        $pluriel = $nb_total > 1 ? 's' : '';
    ?>
    <h3>
        <?= $nb_affichees ?> / <?= $nb_total ?> station<?= $pluriel ?>
        dans le département <strong><?= htmlspecialchars($code_dep) ?></strong>
        — données issues du flux <abbr title="eXtensible Markup Language">XML</abbr>
        <?= $carburant !== '' ? '· <strong>' . htmlspecialchars($carburant) . '</strong>' : '' ?>
    </h3>
 
    <div class="stations-liste">
    <?php foreach (array_slice($stations, 0, $max_stations) as $s) : ?>
        <article class="station-card">
            <div class="station-header">
                <h4><?= htmlspecialchars($s['adresse']) ?></h4>
                <span class="station-ville"><?= htmlspecialchars($s['ville']) ?></span>
                <?php if ($s['automate']) : ?>
                    <span class="badge automate">24h/24</span>
                <?php endif ?>
            </div>
            <div class="prix-grille">
            <?php foreach ($s['prix'] as $type => $valeur) :
                $highlight = ($carburant === $type) ? ' prix-highlight' : '';
            ?>
                <div class="prix-item<?= $highlight ?>">
                    <span class="prix-type"><?= htmlspecialchars($type) ?></span>
                    <span class="prix-valeur"><?= number_format($valeur, 3, ',', '') ?> €/L</span>
                </div>
            <?php endforeach ?>
            </div>
        </article>
    <?php endforeach ?>
    </div>
 
    <?php if ($nb_total > $max_stations) : ?>
        <p class="notice">
            Seules les <?= $max_stations ?> stations les moins chères sont affichées.
            Pour une ville précise, utilisez la <a href="index.php">recherche classique</a>.
        </p>
    <?php endif ?>
</section>
 
<?php elseif ($erreur !== '') : ?>
    <p class="erreur">⚠️ <?= $erreur ?></p>
    <p><a href="index.php">← Effectuer une recherche manuelle</a></p>
<?php endif ?>
 
</main>
 
<?php require_once "include/footer.inc.php"; ?>