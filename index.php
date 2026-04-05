<?php
declare(strict_types=1);
require "include/functions.inc.php";
$title = "EcoPlein";
$style = "/style/newcss.css";
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
        
        <map name = "carte-regions">
            <?php
                foreach ($regions as $region) {
                    echo '<area shape="poly"';
                    echo ' coords="' . scaleCoords($region['coords'], $ratio) . '"';
                    echo ' href="results.php?region=' . $region['code'] . '"';
                    echo ' alt="' . htmlspecialchars($region['nom']) . '" />';
                }
            ?>
        </map>  

    </section>
    <section class="equipe">
        <h3>A Propos Des Auteurs</h3>
        <div>
            <section>
                <img src="images/equipe/SADLIAgnies.png" alt=""/>
                <p>SADLI Agnies</p>
                <p class="role">La meuf</p>
                <blockquote><em>""</em></blockquote>
            </section>
            <section>
                <img src="images/equipe/LHONOREEliott.webp" alt=""/>
                <p>L'HONORE Eliott</p>
                <p class="role">Le bro</p>
                <blockquote><em>"Ad astra per aspera."</em></blockquote>
            </section>
        </div>
    </section>
</main>
<?php require_once "include/footer.inc.php"; ?>
