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
        <h2>Sélectionnez votre région</h2>
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

    <div class="author-container">
        <section class="author-card">
            <div class="author-avatar">
                <img src="images/equipe/SADLIAgnies.png" alt="">
            </div>

            <div class="author-content">
                <p class="author-name">SADLI Agnies</p>
                <p class="author-role">La meuf</p>
                <p class="author-bio">""</p>
            </div>
        </section>

        <section class="author-card">
            <div class="author-avatar">
                <img src="images/equipe/LHONOREEliott.webp" alt="">
            </div>

            <div class="author-content">
                <p class="author-name">L'HONORE Eliott</p>
                <p class="author-role">Le bro</p>
                <p class="author-bio">"Ad astra per aspera."</p>
            </div>
        </section>
    </div>
</section>
</main>
<?php require_once "include/footer.inc.php"; ?>
