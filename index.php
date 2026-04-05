<?php
declare(stricte_types=1);
require "include/functions.inc.php";
$title = "EcoPlein";
$style = "/style/style.css";
require_once "include/header.inc.php";
?>
<section class="hero">
    <aside>
        <h1>EcoPlein</h1>
        <p>Trouvez le carburant au meilleur prix, sans tourner en rond.</p>
    </aside>
    <figure>
        <img src="images/hero.webp" alt="Illustration">
    </figure>
</section>
<main>
   <section id="carte">
        <h2> Sélectionnez votre région </h2>
        <p>Cliquez sur votre région pour commencer la recherche.</p>
        <figure>
            <img src="images/regions.webp" usemap="#carte-regions" alt="Carte interactive des régions de france métropolitaine" id="carte-img" width="<?= $largeur_affichage ?>" />
            <figcaption>Carte des régions de France</figcaption>
        </figure>
        
        <map name = carte-regions>
            <?php foreach ($regions as $region) : ?>
            <area shape="poly"
                coords="<?= scaleCoords($region['coords'], $ratio) ?>"
                href="results.php?region=<?= $region['code'] ?>"
                alt="<?= htmlspecialchars($region['nom']) ?>" />
            <?php endforeach; ?>
        </map>  

    </section>
    <section class="equipe">
        <h3>Equipe du site</h3>
        <div>
            <section>
                <img src="images/SADLIAgnies.png" alt=""/>
                <p>SADLI Agnies</p>
                <p class="role">La meuf</p>
                <blockquote><em>""</em></blockquote>
            </section>
            <section>
                <img src="images/LHONOREEliott.webp" alt=""/>
                <p>L'HONORE Eliott</p>
                <p class="role">Le bro</p>
                <blockquote><em>"Ad astra per aspera."</em></blockquote>
            </section>
        </div>
    </section>
</main>
<?php require_once "include/footer.inc.php"; ?>
