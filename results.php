<?php
declare(strict_types=1);
require "include/functions.inc.php";
$title = "Résultats";
$style = "/style/style.css";
require_once "include/header.inc.php";

$code_region = isset($_GET['region']) ? htmlspecialchars($_GET['region']) : '';
$code_dep    = isset($_GET['dep'])    ? htmlspecialchars($_GET['dep'])    : '';
$ville       = isset($_GET['ville'])  ? htmlspecialchars($_GET['ville'])  : '';
$carburant   = isset($_GET['carburant']) ? htmlspecialchars($_GET['carburant']) : '';

$departements = [];
$communes     = [];

if ($code_region !== '') {
    $departements = getDepartements($code_region);
}

if ($code_dep !== '') {
    $communes = getCommunes($code_dep);
}

// TEST TEMPORAIRE
if ($ville !== '') {
    $stations = getStations($ville, $carburant);
    echo '<pre>';
    print_r($stations);
    echo '</pre>';
    exit;
}
?>

<main>
<h2>Recherche de stations-service</h2>

<form method="get" action="results.php">
    <input type="hidden" name="region" value="<?= $code_region ?>" />

        <?php if (!empty($departements)) : ?>
        <section class="filtre">
            <h3>Sélectionnez votre département</h3>
            <div class="dep-buttons">
                <?php foreach ($departements as $code => $nom) : ?>
                    <a href="results.php?region=<?= $code_region ?>&dep=<?= htmlspecialchars((string)$code) ?>"
                    class="dep-btn <?= ((string)$code === $code_dep ? 'actif' : '') ?>">
                        <span class="dep-num"><?= htmlspecialchars((string)$code) ?></span>
                        <span class="dep-nom"><?= htmlspecialchars($nom) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    <?php if (!empty($communes)) : ?>
    <section class="filtre">
        <label for="ville">Ville :</label>
        <select name="ville" id="ville">
            <option value="">-- Choisissez une ville --</option>
            <?php foreach ($communes as $cp => $nom) : ?>
                <option value="<?= htmlspecialchars($nom) ?>"
                    <?= ($nom === $ville) ? 'selected="selected"' : '' ?>>
                    <?= htmlspecialchars($nom) ?> (<?= htmlspecialchars((string)                                                                                                                            $cp) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </section>

    <section class="filtre">
        <label for="carburant">Carburant :</label>
        <select name="carburant" id="carburant">
            <option value="">-- Tous --</option>
            <option value="SP95"   <?= ($carburant === 'SP95')   ? 'selected="selected"' : '' ?>>SP95</option>
            <option value="SP98"   <?= ($carburant === 'SP98')   ? 'selected="selected"' : '' ?>>SP98</option>
            <option value="Gazole" <?= ($carburant === 'Gazole') ? 'selected="selected"' : '' ?>>Gazole</option>
            <option value="E10"    <?= ($carburant === 'E10')    ? 'selected="selected"' : '' ?>>E10</option>
            <option value="GPL"    <?= ($carburant === 'GPL')    ? 'selected="selected"' : '' ?>>GPL</option>
            <option value="E85"    <?= ($carburant === 'E85')    ? 'selected="selected"' : '' ?>>E85</option>
        </select>
    </section>
    <?php endif; ?>

    <?php if ($code_dep !== '') : ?>
    <section class="filtre">
        <input type="submit" value="Rechercher" />
    </section>
    <?php endif; ?>

</form>

<?php if ($code_region === '' && $ville === '') : ?>
    <p>Veuillez sélectionner une région sur la <a href="index.php">carte d'accueil</a>.</p>
<?php endif; ?>

</main>

<?php require_once "include/footer.inc.php"; ?>