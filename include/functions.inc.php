<?php
    declare(strict_types=1);
    
    define('ROOT', dirname(__DIR__));

    // Largeur originale de l'image
    $largeur_originale = 712 ;
    // Largeur d'affichage choisie
    $largeur_affichage = 500;
    $ratio = $largeur_affichage / $largeur_originale; // = 0.5

    function scaleCoords(string $coords, float $ratio): string {
        $points = explode(',', $coords);
        $scaled = array_map(fn($p) => round((float)$p * $ratio), $points);
        return implode(',', $scaled);
    }

  $regions = [
    ['code' => '94', 'nom' => 'Corse',
     'coords' => '654,638,696,598,701,680,692,719,669,712,654,667'],
    ['code' => '76', 'nom' => 'Occitanie',
     'coords' => '320,496,233,564,233,651,290,647,333,662,361,678,397,683,419,673,418,642,442,612,483,586,510,547,437,486'],
    ['code' => '44', 'nom' => 'Grand Est',
     'coords' => '489,90,471,117,455,137,438,174,438,205,460,238,499,239,566,246,614,244,649,268,650,230,670,181,645,148'],
    ['code' => '93', 'nom' => 'Provence-Alpes-Côte d\'Azur',
     'coords' => '522,545,505,602,609,629,679,551,632,491,614,477'],
    ['code' => '27', 'nom' => 'Bourgogne-Franche-Comté',
     'coords' => '440,219,414,228,413,265,412,294,420,323,433,344,447,343,462,359,469,375,499,378,528,361,581,356,592,335,611,303,623,272,582,248,539,274,500,247'],
    ['code' => '28', 'nom' => 'Normandie',
     'coords' => '189,108,166,116,177,147,178,180,197,197,225,199,265,207,291,218,298,188,337,155,346,125,331,85'],
    ['code' => '53', 'nom' => 'Bretagne',
     'coords' => '190,256,119,277,24,237,23,181,45,181,95,171,194,205'],
    ['code' => '32', 'nom' => 'Hauts-de-France',
     'coords' => '386,8,349,20,340,71,352,109,349,142,376,149,414,154,444,151,466,110,458,63'],
    ['code' => '11', 'nom' => 'Île-de-France',
     'coords' => '392,156,341,156,341,187,357,208,381,216,404,229,416,216,438,198,422,164'],
    ['code' => '24', 'nom' => 'Centre-Val-de-Loire',
     'coords' => '306,192,295,258,262,306,315,362,369,370,413,333,411,243,383,227,352,208,336,181'],
    ['code' => '52', 'nom' => 'Pays-de-la-Loire',
     'coords' => '194,252,126,292,144,343,185,367,269,277,301,243,268,210,207,205'],
    ['code' => '75', 'nom' => 'Nouvelle-Aquitaine',
     'coords' => '258,309,213,321,152,613,333,474,368,472,391,403,388,389,376,375,317,367,274,327'],
    ['code' => '84', 'nom' => 'Auvergne-Rhône-Alpes',
     'coords' => '467,370,413,342,393,394,366,478,401,482,437,481,465,502,513,533,551,518,579,490,597,474,627,453,633,429,626,396,617,365'],
 ];

        /**
     * Charge les départements d'une région depuis le CSV
     * @param string $code_region Le code de la région à filtrer
     * @return array Liste des départements [code => nom]
     */

    function getDepartements(string $code_region): array {
        $departements = [];
        $fichier = fopen(ROOT . '/data/v_departement_2024.csv', 'r');
        
        // saute la ligne d'en-tête
        fgetcsv($fichier, 0, ',', '"', '\\');
        
        while (($ligne =  fgetcsv($fichier, 0, ',', '"', '\\')) !== false) {
            $code_dep = trim($ligne[0], '"');
            $code_reg = trim($ligne[1], '"');
            $nom      = trim($ligne[5], '"');   
        
    

            

            if ($code_reg === $code_region) {
                $departements[$code_dep] = $nom;
            }
        }
        fclose($fichier);
        return $departements;
    }



       function getCommunes(string $code_dep): array {
        $communes = [];
        $fichier = fopen(ROOT . '/data/communes.csv', 'r');

        // saute la ligne d'en-tête (commence par '#')
        fgetcsv($fichier, 0, ';', '"', '\\');

        // communes.csv : ISO-8859, séparateur ';'
        // colonnes : [0] code INSEE, [1] nom, [2] code postal, [3] libellé acheminement
        while (($ligne = fgetcsv($fichier, 0, ';', '"', '\\')) !== false) {
            // ignore les lignes incomplètes
            if (!isset($ligne[2]) || !isset($ligne[3])) continue;

            $code_insee  = trim($ligne[0]);
            $code_postal = trim($ligne[2]);
            // libellé = colonne 3 (souvent identique au nom, meilleure casse)
            $nom = trim($ligne[3]);

            // conversion ISO-8859-1 → UTF-8 pour l'affichage HTML
            $nom = iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $nom);

            // les 2 premiers caractères du code INSEE = code département
            // (fonctionne aussi pour "2A" et "2B" en Corse)
            $dep = substr($code_insee, 0, 2);

            if ($dep === $code_dep) {
                $communes[$code_postal] = $nom;
            }
        }
        fclose($fichier);
        return $communes;
    }



        /**
     * Récupère les stations d'une ville via l'API gouvernementale (JSON)
     * @param string $ville Nom de la ville
     * @param string $carburant Type de carburant filtré (vide = tous)
     * @return array Liste des stations avec leurs prix
     */
   /* function getStations(string $ville, string $carburant = ''): array {

        // l'API attend la casse exacte : "Lyon" pas "LYON"
        $ville_propre  = ucfirst(strtolower($ville));
        $ville_encodee = urlencode($ville_propre);

        $url = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/'
            . 'prix-des-carburants-en-france-flux-instantane-v2/records'
            . '?where=ville%3D%22' . $ville_encodee . '%22'
            . '&limit=50';

        $reponse = file_get_contents($url);

        if ($reponse === false) {
            return [];
        }

        $donnees = json_decode($reponse, true);

        if ($donnees === null || empty($donnees['results'])) {
            return [];
        }

        $stations = [];

        foreach ($donnees['results'] as $item) {

            // construction des prix disponibles uniquement
            $prix = [];
            if ($item['sp95_prix']   !== null) $prix['SP95']   = $item['sp95_prix'];
            if ($item['sp98_prix']   !== null) $prix['SP98']   = $item['sp98_prix'];
            if ($item['gazole_prix'] !== null) $prix['Gazole'] = $item['gazole_prix'];
            if ($item['e10_prix']    !== null) $prix['E10']    = $item['e10_prix'];
            if ($item['gplc_prix']   !== null) $prix['GPL']    = $item['gplc_prix'];
            if ($item['e85_prix']    !== null) $prix['E85']    = $item['e85_prix'];

            // si un carburant est filtré et que la station ne l'a pas → on saute
            if ($carburant !== '' && !isset($prix[$carburant])) {
                continue;
            }

            // station sans aucun prix disponible → on saute
            if (empty($prix)) {
                continue;
            }

            $stations[] = [
                'adresse'   => $item['adresse']?? '',
                'ville'     => $item['ville']?? $ville,
                'automate'  => $item['horaires_automate_24_24'] ?? 'Non',
                'services'  => $item['services_service']     ?? [],
                'prix'      => $prix,
            ];
        }

        return $stations;
    }*/
?>