<?php
    declare(strict_types=1);
    
    define('ROOT', dirname(__DIR__));

    // Largeur originale de l'image
    $largeur_originale = 712;
    // Largeur d'affichage choisie
    $largeur_affichage = 250;
    $ratio = $largeur_affichage / $largeur_originale; // = 0.5

    function scaleCoords(string $coords, float $ratio): string {
        $points = explode(',', $coords);
        $scaled = array_map(fn($p) => round((float)$p * $ratio), $points);
        return implode(',', $scaled);
    }

  $regions = [
    ['code' => '94', 'nom' => 'Corse',
     'coords' => '1562,1029,1539,925,1479,1006,1484,1093,1541,1123'],
    ['code' => '76', 'nom' => 'Occitanie',
     'coords' => '1252,877,1234,823,1163,759,1117,734,950,738,877,861,793,867,791,1020,927,1021,1003,1080,1072,1062'],
    ['code' => '44', 'nom' => 'Grand Est',
     'coords' => '1507,192,1442,169,1359,141,1285,134,1214,65,1145,157,1124,222,1158,330,1271,369,1365,328,1476,386'],
    ['code' => '93', 'nom' => 'Provence-Alpes-Côte d\'Azur',
     'coords' => '1439,950,1225,927,1248,816,1338,778,1409,715,1451,798,1520,828'],
    ['code' => '27', 'nom' => 'Bourgogne-Franche-Comté',
     'coords' => '1423,348,1350,331,1292,382,1239,329,1172,345,1110,279,1082,399,1093,488,1222,555,1266,530,1338,551,1393,475,1430,421'],
    ['code' => '28', 'nom' => 'Normandie',
     'coords' => '940,66,887,84,845,103,815,96,748,112,668,106,686,162,691,240,886,288,897,241,946,212,965,160,974,91'],
    ['code' => '53', 'nom' => 'Bretagne',
     'coords' => '657,223,614,246,592,205,562,198,495,214,415,208,401,267,438,342,607,384,720,332,745,293,729,243,680,237'],
    ['code' => '32', 'nom' => 'Hauts-de-France',
     'coords' => '1178,50,1113,2,1049,2,991,1,966,11,949,57,984,172,1033,182,1098,188,1126,209,1165,147,1188,112'],
    ['code' => '11', 'nom' => 'Île-de-France',
     'coords' => '1102,204,1010,180,976,187,957,201,969,247,990,290,1040,292,1052,319,1082,303,1124,260,1123,228'],
    ['code' => '24', 'nom' => 'Centre-Val-de-Loire',
     'coords' => '909,286,891,329,860,390,837,429,845,457,905,505,932,538,981,542,1031,544,1054,510,1098,489,1080,441,1073,364,1100,348,1031,311,985,276,957,225,907,242'],
    ['code' => '52', 'nom' => 'Pays-de-la-Loire',
     'coords' => '726,317,702,348,621,381,598,420,606,448,635,487,687,543,761,541,745,463,819,444,830,395,859,368,892,341,890,313,850,272,797,265,746,265'],
    ['code' => '75', 'nom' => 'Nouvelle-Aquitaine',
     'coords' => '764,999,796,856,877,833,948,718,994,714,1036,665,1038,561,994,541,904,527,874,480,820,449,749,467,768,538,724,550,704,614,703,743,643,929,695,980'],
    ['code' => '84', 'nom' => 'Auvergne-Rhône-Alpes',
     'coords' => '1082,399,1093,488,1086,491,1017,583,992,774,1143,795,1158,330,1110,279'],
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



        /**
     * Charge les communes d'un département depuis le CSV
     * @param string $code_dep Le code département à filtrer (ex: '01')
     * @return array Liste des communes [code_postal => nom]
     */
    function getCommunes(string $code_dep): array {
        $communes = [];
        $fichier = fopen(ROOT . '/data/communes.csv', 'r');

        // saute la ligne d'en-tête
         fgetcsv($fichier, 0, ',', '"', '\\');

        while (($ligne =  fgetcsv($fichier, 0, ',', '"', '\\')) !== false) {
            $code_insee  = trim($ligne[0], '"');
            $nom = trim($ligne[3], '"');
            $code_postal = trim($ligne[2], '"');

            // les 2 premiers caractères du code INSEE = code département
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
    function getStations(string $ville, string $carburant = ''): array {

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
    }
?>