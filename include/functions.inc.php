<?php
declare(strict_types=1);
 
define('ROOT', dirname(__DIR__));
 
$largeur_originale = 712;
$largeur_affichage = 500;
$ratio = $largeur_affichage / $largeur_originale;
 
function scaleCoords(string $coords, float $ratio): string {
    $points = explode(',', $coords);
    $result = [];
    foreach ($points as $p) {
        $result[] = round((float)$p * $ratio);
    }
    return implode(',', $result);
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
 
function getDepartements(string $code_region): array {
    $departements = [];
    $fichier = fopen(ROOT . '/data/v_departement_2024.csv', 'r');
    fgetcsv($fichier, 0, ',', '"', '\\');
    while (($ligne = fgetcsv($fichier, 0, ',', '"', '\\')) !== false) {
        if (!isset($ligne[0], $ligne[1], $ligne[5]))
            continue;
        $code_dep = trim($ligne[0] ?? '', '"');
        $code_reg = trim($ligne[1] ?? '', '"');
        $nom = trim($ligne[5] ?? '', '"');
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
    fgetcsv($fichier, 0, ';', '"', '\\');
    while (($ligne = fgetcsv($fichier, 0, ';', '"', '\\')) !== false) {
        if (!isset($ligne[0], $ligne[2], $ligne[3]))
            continue;
        $code_postal = trim($ligne[2] ?? '');
        $nom = trim($ligne[3] ?? '');
        if ($nom === '')
            continue;
        $nom = mb_convert_encoding($nom, 'UTF-8', 'ISO-8859-1');
        $dep = substr(trim($ligne[0] ?? ''), 0, 2);
        if ($dep === $code_dep) {
            $communes[$code_postal] = $nom;
        }
    }
    fclose($fichier);
    return $communes;
}
 
/**
 * Retourne les stations d'une ville via l'API gouvernementale des carburants
 * @param string $ville
 * @param string $code_dep
 * @param string $carburant filtre optionnel (SP95, SP98, Gazole, E10, GPL, E85)
 * @return array stations triées par prix croissant
 */
function getStations(string $ville, string $code_dep, string $carburant = ''): array {
    $vn = strtoupper($ville);
 
    $url_base = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/'
        . 'prix-des-carburants-en-france-flux-instantane-v2/records'
        . '?where=' . urlencode('code_departement="' . $code_dep . '"')
        . '&limit=100';
 
    $reponse = @file_get_contents($url_base . '&offset=0');
    if (!$reponse)
        return [];
 
    $donnees = json_decode($reponse, true);
    if (empty($donnees['results']))
        return [];
 
    $result = $donnees['results'];
    $total = (int)($donnees['total_count'] ?? 0);
    $offset = 100;
 
    while ($offset < $total) {
        $reponse = @file_get_contents($url_base . '&offset=' . $offset);
        if (!$reponse)
            break;
        $page = json_decode($reponse, true);
        if (empty($page['results']))
            break;
        foreach ($page['results'] as $r) {
            $result[] = $r;
        }
        $offset += 100;
    }
 
    $stations = [];
    foreach ($result as $item) {
        if (strtoupper($item['ville'] ?? '') !== $vn)
            continue;
 
        $prix = [];
        if (!empty($item['sp95_prix'])) $prix['SP95'] = (float)$item['sp95_prix'];
        if (!empty($item['sp98_prix'])) $prix['SP98'] = (float)$item['sp98_prix'];
        if (!empty($item['gazole_prix'])) $prix['Gazole'] = (float)$item['gazole_prix'];
        if (!empty($item['e10_prix'])) $prix['E10'] = (float)$item['e10_prix'];
        if (!empty($item['gplc_prix'])) $prix['GPL'] = (float)$item['gplc_prix'];
        if (!empty($item['e85_prix'])) $prix['E85'] = (float)$item['e85_prix'];
 
        if ($carburant !== '' && !isset($prix[$carburant]))
            continue;
        if (empty($prix))
            continue;
 
        $maj = [];
        foreach (['sp95'=>'SP95','sp98'=>'SP98','gazole'=>'Gazole','e10'=>'E10','gplc'=>'GPL','e85'=>'E85'] as $k => $label) {
            if (!empty($item[$k.'_maj'])) $maj[$label] = substr($item[$k.'_maj'], 0, 10);
        }
 
        $stations[] = [
            'adresse' => $item['adresse'] ?? '',
            'ville' => $item['ville'] ?? $ville,
            'automate' => ($item['horaires_automate_24_24'] ?? '') === 'Oui',
            'prix' => $prix,
            'maj' => $maj,
        ];
    }
 
    // tri à bulles par prix croissant
    $cle_tri = ($carburant !== '') ? $carburant : 'Gazole';
    $nb = count($stations);
    for ($i = 0; $i < $nb - 1; $i++) {
        for ($j = 0; $j < $nb - $i - 1; $j++) {
            $pa = $stations[$j]['prix'][$cle_tri] ?? 9999;
            $pb = $stations[$j+1]['prix'][$cle_tri] ?? 9999;
            if ($pa > $pb) {
                $tmp = $stations[$j];
                $stations[$j] = $stations[$j+1];
                $stations[$j+1] = $tmp;
            }
        }
    }
 
    return $stations;
}
 
function logVille(string $ville, string $code_dep, string $carburant = ''): void {
    $fichier = ROOT . '/data/consultations.csv';
    if (!file_exists($fichier)) {
        file_put_contents($fichier, "horodatage,ville,code_departement,carburant\n", LOCK_EX);
    }
    $horodatage = date('Y-m-d H:i:s');
    $ligne = sprintf("%s,%s,%s,%s\n",
        $horodatage,
        str_replace(',', ' ', $ville),
        $code_dep,
        $carburant
    );
    file_put_contents($fichier, $ligne, FILE_APPEND | LOCK_EX);
}
 
function getTopVilles(int $top = 10): array {
    $fichier = ROOT . '/data/consultations.csv';
    if (!file_exists($fichier))
        return [];
    $fp = fopen($fichier, 'r');
    fgetcsv($fp); // en-tête
    $compteur = [];
    while (($ligne = fgetcsv($fp)) !== false) {
        if (!isset($ligne[1]))
            continue;
        $ville = trim($ligne[1]);
        if ($ville === '')
            continue;
        $compteur[$ville] = ($compteur[$ville] ?? 0) + 1;
    }
    fclose($fp);
    arsort($compteur);
    return array_slice($compteur, 0, $top, true);
}
 
function getTotalConsultations(): int {
    $fichier = ROOT . '/data/consultations.csv';
    if (!file_exists($fichier))
        return 0;
    $lines = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return max(0, count($lines) - 1);
}
 
function getGeoFromIP(): array {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
 
    // derrière un proxy le header peut contenir plusieurs IPs
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
 
    if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1') {
        return [];
    }
 
    $json = @file_get_contents("https://ipinfo.io/{$ip}/geo");
    if (!$json)
        return [];
    return json_decode($json, true) ?? [];
}
 
function codeDepDepuisPostal(string $postal): string {
    $postal = trim($postal);
    if (strlen($postal) < 2)
        return '';
 
    // DOM
    $debut3 = substr($postal, 0, 3);
    if (in_array($debut3, ['971', '972', '973', '974', '976'], true)) {
        return $debut3;
    }
 
    // corse
    $debut2 = substr($postal, 0, 2);
    if ($debut2 === '20') {
        return '2A';
    }
 
    return $debut2;
}
 
/**
 * Récupère les stations d'un département via le flux KML (XML) de l'API gouvernementale.
 * On parse le XML manuellement avec preg_match_all car SimpleXML n'est pas dispo sur le serveur.
 * Chaque <Placemark> correspond à une station, les données sont dans des balises <SimpleData>.
 */
function getStationsParDepXML(string $code_dep, int $limit = 50, string $carburant = ''): array {
    $url = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/'
        . 'prix-des-carburants-en-france-flux-instantane-v2/exports/kml'
        . '?where=' . urlencode('code_departement="' . $code_dep . '"')
        . '&limit=' . $limit;
 
    $xml_raw = @file_get_contents($url);
    if (!$xml_raw) return [];
 
    $stations = [];
    preg_match_all('/<Placemark>(.*?)<\/Placemark>/s', $xml_raw, $blocs);
 
    foreach ($blocs[1] as $bloc) {

        preg_match('/<SimpleData name="sp95_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_sp95 = trim($m[1] ?? '');
        preg_match('/<SimpleData name="sp98_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_sp98 = trim($m[1] ?? '');
        preg_match('/<SimpleData name="gazole_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_gaz = trim($m[1] ?? '');
        preg_match('/<SimpleData name="e10_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_e10 = trim($m[1] ?? '');
        preg_match('/<SimpleData name="gplc_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_gpl = trim($m[1] ?? '');
        preg_match('/<SimpleData name="e85_prix">(.*?)<\/SimpleData>/s', $bloc, $m); $v_e85 = trim($m[1] ?? '');
        preg_match('/<SimpleData name="adresse">(.*?)<\/SimpleData>/s', $bloc, $m); $v_adr = trim($m[1] ?? '');
        preg_match('/<SimpleData name="ville">(.*?)<\/SimpleData>/s', $bloc, $m); $v_ville = trim($m[1] ?? '');
        preg_match('/<SimpleData name="horaires_automate_24_24">(.*?)<\/SimpleData>/s', $bloc, $m); $v_auto = trim($m[1] ?? '');

        $prix = [];
        if ($v_sp95 !== '') $prix['SP95'] = (float)$v_sp95;
        if ($v_sp98 !== '') $prix['SP98'] = (float)$v_sp98;
        if ($v_gaz !== '') $prix['Gazole'] = (float)$v_gaz;
        if ($v_e10 !== '') $prix['E10'] = (float)$v_e10;
        if ($v_gpl !== '') $prix['GPL'] = (float)$v_gpl;
        if ($v_e85 !== '') $prix['E85'] = (float)$v_e85;

        if ($carburant !== '' && !isset($prix[$carburant]))
            continue;
        if (empty($prix))
            continue;

        $stations[] = [
            'adresse' => $v_adr,
            'ville' => $v_ville,
            'automate' => $v_auto === 'Oui',
            'prix' => $prix,
        ];
    }
 
    // tri à bulles par prix croissant
    $cle_tri = ($carburant !== '') ? $carburant : 'Gazole';
    $nb = count($stations);
    for ($i = 0; $i < $nb - 1; $i++) {
        for ($j = 0; $j < $nb - $i - 1; $j++) {
            $pa = $stations[$j]['prix'][$cle_tri] ?? 9999;
            $pb = $stations[$j+1]['prix'][$cle_tri] ?? 9999;
            if ($pa > $pb) {
                $tmp = $stations[$j];
                $stations[$j] = $stations[$j+1];
                $stations[$j+1] = $tmp;
            }
        }
    }
 
    return $stations;
}