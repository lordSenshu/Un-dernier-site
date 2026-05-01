<?php
declare(strict_types=1);
require "include/functions.inc.php";
$title = "Résultats";
require_once "include/header.inc.php";

$code_region = isset($_GET['region']) ? htmlspecialchars($_GET['region']) : '';
$code_dep = isset($_GET['dep']) ? htmlspecialchars($_GET['dep']) : '';
$ville = isset($_GET['ville']) ? htmlspecialchars($_GET['ville']) : '';
$carburant = isset($_GET['carburant']) ? htmlspecialchars($_GET['carburant']) : '';

$departements = [];
$communes = [];
$stations = [];

if ($code_region !== '') $departements = getDepartements($code_region);
if ($code_dep !== '') $communes = getCommunes($code_dep);

$cookie_ville = 'ecoplein_derniere_ville';
$cookie_path_app = '/';

$derniere_ville = '';
if (isset($_COOKIE[$cookie_ville])) {
    $val = json_decode($_COOKIE[$cookie_ville], true);
    if (is_array($val) && !empty($val['ville'])) {
        $derniere_ville = $val;
    } else {
        setcookie($cookie_ville, '', time() - 3600, $cookie_path_app);
    }
}

if ($ville !== '' && $code_dep !== '') {
    $stations = getStations($ville, $code_dep, $carburant);

    logVille($ville, $code_dep, $carburant);

    $cookie_data = json_encode([
        'ville' => $ville,
        'dep' => $code_dep,
        'region' => $code_region,
        'carburant' => $carburant,
        'date' => date('d/m/Y H:i'),
    ]);
    setcookie($cookie_ville, $cookie_data, time() + 30 * 24 * 3600, $cookie_path_app);
}
?>

<main>
<h2>Recherche de stations-service</h2>

<?php if ($derniere_ville && $ville === '') : ?>
<section class="derniere-visite">
    <p>
        Dernière recherche : <strong><?= htmlspecialchars($derniere_ville['ville']) ?></strong>
        (<?= htmlspecialchars($derniere_ville['date'] ?? '') ?>)
        - <a href="results.php?region=<?= urlencode($derniere_ville['region'] ?? '') ?>&dep=<?= urlencode($derniere_ville['dep'] ?? '') ?>&ville=<?= urlencode($derniere_ville['ville']) ?>&carburant=<?= urlencode($derniere_ville['carburant'] ?? '') ?>">
            Relancer la recherche
        </a>
    </p>
</section>
<?php endif; ?>

<form method="get" action="results.php">
    <input type="hidden" name="region" value="<?= $code_region ?>" />
    <input type="hidden" name="dep" value="<?= $code_dep ?>" />

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
                    <?= htmlspecialchars($nom) ?> (<?= htmlspecialchars((string)$cp) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </section>

    <section class="filtre">
        <label for="carburant">Carburant :</label>
        <select name="carburant" id="carburant">
            <option value="">-- Tous --</option>
            <option value="SP95" <?= ($carburant === 'SP95') ? 'selected="selected"' : '' ?>>SP95</option>
            <option value="SP98" <?= ($carburant === 'SP98')  ? 'selected="selected"' : '' ?>>SP98</option>
            <option value="Gazole" <?= ($carburant === 'Gazole') ? 'selected="selected"' : '' ?>>Gazole</option>
            <option value="E10" <?= ($carburant === 'E10') ? 'selected="selected"' : '' ?>>E10</option>
            <option value="GPL" <?= ($carburant === 'GPL') ? 'selected="selected"' : '' ?>>GPL</option>
            <option value="E85" <?= ($carburant === 'E85') ? 'selected="selected"' : '' ?>>E85</option>
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

<?php elseif ($ville !== '' && empty($stations)) : ?>
    <p class="aucun-resultat">Aucune station trouvée pour <strong><?= htmlspecialchars($ville) ?></strong>
    <?= ($carburant !== '') ? '(carburant : ' . htmlspecialchars($carburant) . ')' : '' ?>.
    <br />Essayez sans filtre ou vérifiez la ville.</p>

<?php elseif (!empty($stations)) : ?>
    <section class="resultats">
        <h3>
            <?= count($stations) ?> station<?= count($stations) > 1 ? 's' : '' ?>
            trouvée<?= count($stations) > 1 ? 's' : '' ?>
            à <strong><?= htmlspecialchars($ville) ?></strong>
            <?= ($carburant !== '') ? '· <strong>' . htmlspecialchars($carburant) . '</strong>' : '' ?>
        </h3>

        <div class="stations-liste">
        <?php foreach ($stations as $s) : ?>
            <article class="station-card">
                <div class="station-header">
                    <h4><?= htmlspecialchars($s['adresse']) ?></h4>
                    <?php if ($s['automate']) : ?>
                        <span class="badge automate">24h/24</span>
                    <?php endif; ?>
                </div>
                <div class="prix-grille">
                <?php foreach ($s['prix'] as $type => $valeur) : ?>
                    <div class="prix-item <?= ($carburant === $type) ? 'prix-highlight' : '' ?>">
                        <span class="prix-type"><?= htmlspecialchars($type) ?></span>
                        <span class="prix-valeur"><?= number_format($valeur, 3, ',', '') ?> €/L</span>
                        <?php if (isset($s['maj'][$type])) : ?>
                            <span class="prix-maj">màj <?= htmlspecialchars($s['maj'][$type]) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
</main>

<?php require_once "include/footer.inc.php"; ?>