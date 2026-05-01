<?php
    declare(strict_types=1);
    
    define('ROOT', dirname(__DIR__));

    $largeur_originale = 712;
    $largeur_affichage = 500;
    $ratio = $largeur_affichage / $largeur_originale;

    /**
     * Met à l'échelle les coordonnées d'une zone cliquable
     * @param string $coords Coordonnées brutes séparées par des virgules
     * @param float  $ratio  Facteur d'échelle à appliquer
     * @return string        Coordonnées mises à l'échelle
     */
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
     * Effectue une requête HTTP GET avec curl (fallback file_get_contents)
     * @param string $url URL à appeler
     * @return string|false Corps de la réponse, ou false en cas d'échec
     */
    function httpGet(string $url): string|false {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'EcoPlein/1.0',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING       => '',
            ]);
            $reponse = curl_exec($ch);
            $erreur  = curl_errno($ch);
            curl_close($ch);
            if ($erreur === 0 && $reponse !== false && $reponse !== '') {
                return $reponse;
            }
        }

        $contexte = stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'EcoPlein/1.0', 'header' => 'Accept-Encoding: gzip'],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $raw = @file_get_contents($url, false, $contexte);
        if ($raw === false) return false;
        if (substr($raw, 0, 2) === "\x1f\x8b") {
            $raw = gzdecode($raw);
        }
        return $raw;
    }

    /**
     * Charge les départements d'une région depuis le CSV
     * @param string $code_region Code de la région
     * @return array Liste [code_dep => nom]
     */
    function getDepartements(string $code_region): array {
        $departements = [];
        $fichier = fopen(ROOT . '/data/v_departement_2024.csv', 'r');
        fgetcsv($fichier, 0, ',', '"', '\\');
        while (($ligne = fgetcsv($fichier, 0, ',', '"', '\\')) !== false) {
            if (!isset($ligne[0], $ligne[1], $ligne[5])) continue;
            $code_dep = trim($ligne[0] ?? '', '"');
            $code_reg = trim($ligne[1] ?? '', '"');
            $nom      = trim($ligne[5] ?? '', '"');
            if ($code_reg === $code_region) {
                $departements[$code_dep] = $nom;
            }
        }
        fclose($fichier);
        return $departements;
    }

    /**
     * Charge les communes d'un département depuis le CSV
     * @param string $code_dep Code du département (ex. "75", "2A")
     * @return array Liste [code_postal => nom_commune]
     */
    function getCommunes(string $code_dep): array {
        $communes = [];
        $fichier = fopen(ROOT . '/data/communes.csv', 'r');
        fgetcsv($fichier, 0, ';', '"', '\\');
        while (($ligne = fgetcsv($fichier, 0, ';', '"', '\\')) !== false) {
            if (!isset($ligne[0], $ligne[2], $ligne[3])) continue;
            $code_postal = trim($ligne[2] ?? '');
            $nom         = trim($ligne[3] ?? '');
            if ($nom === '') continue;
            $nom = iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $nom);
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
     * @param string $ville     Nom de la ville (format CSV : majuscules sans accents)
     * @param string $code_dep  Code du département (ex. "78", "2A")
     * @param string $carburant Carburant filtré — 'SP95', 'SP98', 'Gazole', 'E10', 'GPL', 'E85' (vide = tous)
     * @return array            Stations triées par prix croissant, chacune avec 'adresse', 'automate', 'prix', 'maj'
     */
    function getStations(string $ville, string $code_dep, string $carburant = ''): array {
        $normalise = function(string $s): string {
            $s = mb_strtoupper($s, 'UTF-8');
            $s = strtr($s, [
                'À'=>'A','Â'=>'A','Ä'=>'A','Á'=>'A','Ã'=>'A','Å'=>'A',
                'Ç'=>'C',
                'È'=>'E','É'=>'E','Ê'=>'E','Ë'=>'E',
                'Î'=>'I','Ï'=>'I','Ì'=>'I','Í'=>'I',
                'Ô'=>'O','Ö'=>'O','Ò'=>'O','Ó'=>'O','Õ'=>'O',
                'Ù'=>'U','Ú'=>'U','Û'=>'U','Ü'=>'U',
                'Ÿ'=>'Y','-'=>' ','\''=>' ',
            ]);
            return trim($s);
        };

        $ville_normalisee = $normalise($ville);

        $url_base = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/'
            . 'prix-des-carburants-en-france-flux-instantane-v2/records'
            . '?where=' . rawurlencode('code_departement="' . $code_dep . '"')
            . '&limit=100';

        $reponse = httpGet($url_base . '&offset=0');
        if (!$reponse) return [];
        $donnees = json_decode($reponse, true);
        if (empty($donnees['results'])) return [];

        $tous_resultats = $donnees['results'];
        $total = (int)($donnees['total_count'] ?? 0);
        $offset = 100;
        while ($offset < $total) {
            $reponse = httpGet($url_base . '&offset=' . $offset);
            if (!$reponse) break;
            $page = json_decode($reponse, true);
            if (empty($page['results'])) break;
            $tous_resultats = array_merge($tous_resultats, $page['results']);
            $offset += 100;
        }

        $stations = [];
        foreach ($tous_resultats as $item) {
            if ($normalise($item['ville'] ?? '') !== $ville_normalisee) continue;

            $prix = [];
            if (!empty($item['sp95_prix']))   $prix['SP95']   = (float)$item['sp95_prix'];
            if (!empty($item['sp98_prix']))   $prix['SP98']   = (float)$item['sp98_prix'];
            if (!empty($item['gazole_prix'])) $prix['Gazole'] = (float)$item['gazole_prix'];
            if (!empty($item['e10_prix']))    $prix['E10']    = (float)$item['e10_prix'];
            if (!empty($item['gplc_prix']))   $prix['GPL']    = (float)$item['gplc_prix'];
            if (!empty($item['e85_prix']))    $prix['E85']    = (float)$item['e85_prix'];

            if ($carburant !== '' && !isset($prix[$carburant])) continue;
            if (empty($prix)) continue;

            $maj = [];
            foreach (['sp95'=>'SP95','sp98'=>'SP98','gazole'=>'Gazole','e10'=>'E10','gplc'=>'GPL','e85'=>'E85'] as $k => $label) {
                if (!empty($item[$k.'_maj'])) $maj[$label] = substr($item[$k.'_maj'], 0, 10);
            }

            $stations[] = [
                'adresse'  => $item['adresse'] ?? '',
                'ville'    => $item['ville']   ?? $ville,
                'automate' => ($item['horaires_automate_24_24'] ?? '') === 'Oui',
                'prix'     => $prix,
                'maj'      => $maj,
            ];
        }

        $cle_tri = ($carburant !== '') ? $carburant : 'Gazole';
        usort($stations, function($a, $b) use ($cle_tri) {
            $pa = $a['prix'][$cle_tri] ?? PHP_INT_MAX;
            $pb = $b['prix'][$cle_tri] ?? PHP_INT_MAX;
            return $pa <=> $pb;
        });

        return $stations;
    }

    /**
     * Enregistre une consultation de ville dans le fichier CSV de log
     * @param string $ville     Nom de la ville consultée
     * @param string $code_dep  Code du département
     * @param string $carburant Carburant recherché (vide = tous)
     * @return void
     */
    function logVille(string $ville, string $code_dep, string $carburant = ''): void {
        $fichier = ROOT . '/data/consultations.csv';
        if (!file_exists($fichier)) {
            file_put_contents($fichier, "horodatage,ville,code_departement,carburant\n", LOCK_EX);
        }
        $horodatage = date('Y-m-d H:i:s');
        $ligne = sprintf(
            "%s,%s,%s,%s\n",
            $horodatage,
            str_replace(',', ' ', $ville),
            $code_dep,
            $carburant
        );
        file_put_contents($fichier, $ligne, FILE_APPEND | LOCK_EX);
    }

    /**
     * Retourne les N villes les plus consultées d'après le CSV de log
     * @param int $top Nombre de villes à retourner
     * @return array   Tableau associatif [ville => nombre] trié décroissant
     */
    function getTopVilles(int $top = 10): array {
        $fichier = ROOT . '/data/consultations.csv';
        if (!file_exists($fichier)) return [];
        $fp = fopen($fichier, 'r');
        fgetcsv($fp);
        $compteur = [];
        while (($ligne = fgetcsv($fp)) !== false) {
            if (!isset($ligne[1])) continue;
            $ville = trim($ligne[1]);
            if ($ville === '') continue;
            $compteur[$ville] = ($compteur[$ville] ?? 0) + 1;
        }
        fclose($fp);
        arsort($compteur);
        return array_slice($compteur, 0, $top, true);
    }

    /**
     * Retourne le nombre total de consultations enregistrées
     * @return int
     */
    function getTotalConsultations(): int {
        $fichier = ROOT . '/data/consultations.csv';
        if (!file_exists($fichier)) return 0;
        $lines = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return max(0, count($lines) - 1);
    }

        /**
     * Retourne la géolocalisation approximative de l'utilisateur via ipinfo.io.
     *
     * @return array Tableau avec les clés 'ip', 'city', 'region', 'country', 'postal', 'loc'.
     *               Tableau vide si l'appel échoue.
     */
    function getGeoFromIP(): array {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '') {
            $ip = '193.54.115.192'; // fallback CY Cergy en local
        }
        $json = httpGet("https://ipinfo.io/{$ip}/geo");
        if (!$json) return [];
        return json_decode($json, true) ?? [];
    }

    /**
     * Déduit le code département depuis un code postal français.
     *
     * Gère les cas particuliers : Corse (2A/2B) et DOM (971-976).
     *
     * @param string $postal Code postal (ex. "76600", "20000", "97100").
     * @return string         Code département (ex. "76", "2A", "971") ou "" si invalide.
     */
    function codeDepDepuisPostal(string $postal): string {
        $postal = trim($postal);
        if (strlen($postal) < 2) return '';

        $debut3 = substr($postal, 0, 3);
        if (in_array($debut3, ['971', '972', '973', '974', '976'], true)) {
            return $debut3;
        }

        $debut2 = substr($postal, 0, 2);
        if ($debut2 === '20') {
            return '2A'; // on ne peut pas distinguer 2A/2B depuis le CP seul
        }

        return $debut2;
    }

    /**
     * Récupère les stations d'un département via le flux XML de l'API gouvernementale
     * (data.economie.gouv.fr), parsé avec SimpleXML.
     *
     * Illustre l'exploitation du format XML en complément des appels JSON de getStations().
     *
     * @param string $code_dep  Code du département (ex. "76", "2A").
     * @param int    $limit     Nombre maximum de stations à récupérer (défaut 50).
     * @param string $carburant Filtre optionnel (ex. "SP95", "Gazole"). Vide = tous.
     * @return array            Stations triées par prix croissant,
     *                          chacune avec 'adresse', 'ville', 'automate', 'prix'.
     */
    function getStationsParDepXML(string $code_dep, int $limit = 50, string $carburant = ''): array {
        $url = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/'
            . 'prix-des-carburants-en-france-flux-instantane-v2/exports/kml'
            . '?where=' . rawurlencode('code_departement="' . $code_dep . '"')
            . '&limit=' . $limit;

        $xml_raw = httpGet($url);
        if (!$xml_raw) return [];

        // Retire le namespace KML pour simplifier le parsing SimpleXML
        $xml_raw = str_replace(' xmlns="http://www.opengis.net/kml/2.2"', '', $xml_raw);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_raw);
        if ($xml === false) {
            file_put_contents(
                ROOT . '/data/xml_debug.log',
                date('Y-m-d H:i:s') . " simplexml_load_string failed\n" . substr($xml_raw, 0, 500) . "\n",
                FILE_APPEND
            );
            return [];
        }

        // Structure KML : kml > Document > Placemark[] (sans Folder intermédiaire)
        $placemarks = $xml->Document->Placemark ?? null;
        if ($placemarks === null || count($placemarks) === 0) {
            file_put_contents(
                ROOT . '/data/xml_debug.log',
                date('Y-m-d H:i:s') . " No Placemarks found. XML head: " . substr($xml_raw, 0, 500) . "\n",
                FILE_APPEND
            );
            return [];
        }

        $stations = [];
        foreach ($placemarks as $placemark) {
            // Chaque Placemark > ExtendedData > SchemaData > SimpleData[@name]
            $data = [];
            foreach ($placemark->ExtendedData->SchemaData->SimpleData as $sd) {
                $name        = (string)($sd->attributes()['name'] ?? '');
                $data[$name] = (string)$sd;
            }

            $adresse = trim($data['adresse'] ?? '');
            $ville   = trim($data['ville']   ?? '');

            $prix = [];
            if (!empty($data['sp95_prix']))   $prix['SP95']   = (float)$data['sp95_prix'];
            if (!empty($data['sp98_prix']))   $prix['SP98']   = (float)$data['sp98_prix'];
            if (!empty($data['gazole_prix'])) $prix['Gazole'] = (float)$data['gazole_prix'];
            if (!empty($data['e10_prix']))    $prix['E10']    = (float)$data['e10_prix'];
            if (!empty($data['gplc_prix']))   $prix['GPL']    = (float)$data['gplc_prix'];
            if (!empty($data['e85_prix']))    $prix['E85']    = (float)$data['e85_prix'];

            if ($carburant !== '' && !isset($prix[$carburant])) continue;
            if (empty($prix)) continue;

            $stations[] = [
                'adresse'  => $adresse,
                'ville'    => $ville,
                'automate' => ($data['horaires_automate_24_24'] ?? '') === 'Oui',
                'prix'     => $prix,
            ];
        }

        $cle_tri = ($carburant !== '') ? $carburant : 'Gazole';
        usort($stations, static fn($a, $b) =>
            ($a['prix'][$cle_tri] ?? PHP_INT_MAX) <=> ($b['prix'][$cle_tri] ?? PHP_INT_MAX)
        );

        return $stations;
    }
?>