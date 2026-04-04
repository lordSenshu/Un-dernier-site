<?php
    include_once "util.inc.php"; 
?>
<footer>
    <section>
        <h4>Liens utile</h4>
        <a href="index.php">Index</a>
        <a href="sitemap.php">Sitemap</a>
        <a href="ecologie.php">Ecologie</a>
        <a href="tech.php">API GHIBLI</a>
    </section>
    <section>
        <img src="images/logo_cy.png"/>
        <h4>CY Cergy Paris Université</h4>
        <address>33 Boulevard du Port 95011 Cergy-Pontoise</address>
    </section>
    <section>
        <p>Hebergé chez ⚒️ <a href="https://arbusmegacorp.top">Arbus MegaCorporation</a>.</p>
        <p>Navigateur: <?= get_navigateur() ?></p>
        <?php
        $fichier = __DIR__ . "/../ressources/count.txt";

        $compteur = (int) file_get_contents($fichier, true);
        $compteur++;

        file_put_contents($fichier, $compteur);
        ?>
        <p>Nombre de visites : <?= $compteur ?></p>
    </section>

</footer>
</body>
</html>
