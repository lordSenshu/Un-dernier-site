<?php
declare(strict_types=1);
require "include/functions.inc.php";
$title = "Statistiques";
require_once "include/header.inc.php";

$top_villes   = getTopVilles(10);
$total        = getTotalConsultations();
$max_consulte = !empty($top_villes) ? max($top_villes) : 1;
?>

<main>
<h2>Statistiques d'utilisation</h2>

<section class="stats-total">
    <div class="stat-card">
        <span class="stat-nombre"><?= $total ?></span>
        <span class="stat-label">consultation<?= $total > 1 ? 's' : '' ?> enregistrée<?= $total > 1 ? 's' : '' ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-nombre"><?= count($top_villes) ?></span>
        <span class="stat-label">ville<?= count($top_villes) > 1 ? 's' : '' ?> différente<?= count($top_villes) > 1 ? 's' : '' ?> recherchée<?= count($top_villes) > 1 ? 's' : '' ?></span>
    </div>
</section>

<section class="stats-histo">
    <h3>Top <?= count($top_villes) ?> des villes les plus consultées</h3>

    <?php if (empty($top_villes)) : ?>
        <p>Aucune donnée pour le moment. Lancez une première recherche !</p>
    <?php else : ?>
        <div class="histogramme" aria-label="Histogramme des villes les plus consultées">
        <?php $rang = 1; foreach ($top_villes as $ville => $nb) :
            $pourcent = round($nb / $max_consulte * 100);
            $podium   = $rang <= 3 ? 'podium-' . $rang : '';
        ?>
            <div class="barre-ligne">
                <span class="barre-label"><?= htmlspecialchars($ville) ?></span>
                <div class="barre-conteneur">
                    <div class="barre <?= $podium ?>"
                         style="--pct:<?= $pourcent ?>%"
                         role="progressbar"
                         aria-valuenow="<?= $nb ?>"
                         aria-valuemin="0"
                         aria-valuemax="<?= $max_consulte ?>">
                    </div>
                    <span class="barre-valeur"><?= $nb ?> visite<?= $nb > 1 ? 's' : '' ?></span>
                </div>
            </div>
        <?php $rang++; endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>

<?php require_once "include/footer.inc.php"; ?>