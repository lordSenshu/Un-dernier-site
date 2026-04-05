<!DOCTYPE HTML>
<html lang="fr">
    <head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
		<meta name="description" content ="<?=$description ?? ''?>"/>
		<title><?= $title ?></title>
		<link rel="icon" href=images/logo.ico/>
		<link rel="stylesheet" href="<?php echo $style; ?>"/>
    </head>

    <body>
        <header>
    	<img src ="images/logo.png" alt="le logo de notre site"/>
    	<nav aria-label="Navigation principale">
        	<ul>
                <li><a href="index.php">Page d'acceuil </a></li>
                <li><a href="result.php">Prix carburant </a></li>
                <li><a href="stats.php">Statistiques</a></li>
        	</ul>
    	</nav>
		</header>
