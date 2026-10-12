<?php

ini_set('display_errors', 'on');

$host = 'www.ma-cave-a-vin.fr';

// requis pour la recuperation des parametres d'execution
extract($_REQUEST);
extract($_COOKIE);

include_once('../../configurations/' . $host . '/config_mysql.php');
include('../../class/mysql.php');
include('xlexport.php');
include('ooo.php');
if (!class_exists('ooo')) {
    exit('ooo.php non chargé');
}
if (!isset($_GET['du']) || !isset($_GET['au'])) {
    exit('du/au manquants : du=' . ($_GET['du'] ?? 'absent') . ' au=' . ($_GET['au'] ?? 'absent'));
}

// connection a la base de donnee, attention semble requis pour l'utilisation librairie ooo.php !
$DB_site = new DB_Sql;
$DB_site->appname = 'boutique';
$DB_site->appshortname = 'boutique';
$DB_site->database = $DBname;
$DB_site->server = $db_server;
$DB_site->user = $DBusername;
$DB_site->password = $DBpassword;
$DB_site->connect();

// Verification du user qui execute le script, pour eviter des telechargement par le site web
// A desactiver pour les tests manuels !!!
/*if (isset($admin_username) && $admin_username != '') {
    if ($admin_username != '9d3bb895f22bf0afa958d68c2a58ded7') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $acces = $DB_site->query_first("
            SELECT *
            FROM acces_admin
            WHERE ip = '{$ip}'
              AND user = '{$admin_username}'
              AND connecte = 1
            ORDER BY timestamp DESC
            LIMIT 0,1
        ");
        if ($acces['timestamp'] == false || $acces['timestamp'] < time() - 3600) {
            exit;
        } else {
            $user_info = $DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '{$admin_username}'");
            if ($user_info['username'] != $admin_username) {
                exit;
            }
        }
    }
} else {
    print_r('Erreur !! ');
    exit;
}*/


// Creation de l'Objet Open Office
$ooo = new ooo();

$sp = explode('/', $_GET['du']);
$du = mktime(0, 0, 0, $sp[1], $sp[0], $sp[2]);
$sp = explode('/', $_GET['au']);
$au = mktime(23, 59, 59, $sp[1], $sp[0], $sp[2]);

$periode = date('Ymd', $du) . '-' . date('Ymd', $au);
$ooo->name = 'FacilitePaiement_' . $periode;
$ooo_1 = new ooo_table('FacilitePaiement_' . $periode);

// Mise en place du formatage ligne et colonnes
$ooo_1->set_row(1, '8mm');

$ooo_1->set_col(1, '3cm'); // Fact id
$ooo_1->set_col(2, '3cm'); // Fact num
$ooo_1->set_col(3, '3cm'); // Reglmt num
$ooo_1->set_col(4, '8cm'); // Nom
$ooo_1->set_col(5, '8cm'); // Prenom
$ooo_1->set_col(6, '10cm'); // Adresse
$ooo_1->set_col(7, '3cm'); // CP
$ooo_1->set_col(8, '8cm'); // Ville
$ooo_1->set_col(9, '4cm'); // Montant
$ooo_1->set_col(10, '4cm'); // Reglmt date
$ooo_1->set_col(11, '3cm'); // Reglmt Type


$sql = "SELECT
            f.facturid,
            f.numerofacture,
            CONCAT('0', r.reglement_id) AS numeroreglement,
            f.nom,
            f.prenom,
            f.adresse,
            f.codepostal,
            f.ville,
            r.reglement_montant,
            r.reglement_date,
            m.libelleFR,
			r.reglement_commentaires
        FROM reglement r
        INNER JOIN facture f ON r.facturid = f.facturid
        INNER JOIN moyenpaiement m ON r.reglement_moyen = m.moyenid
        WHERE f.moyenid = 99
          AND f.valide IN (1, 5, 7, 8)

          AND UNIX_TIMESTAMP(r.reglement_date) BETWEEN {$du} AND {$au}
        ORDER BY f.facturid, f.nom, f.numerofacture";
//echo $sql;
//AND r.reglement_moyen IN (1, 8, 9, 10, 12, 13, 16)

$requete = $DB_site->query($sql);

// Insertion dans l'onglet ooo_1 des donnees recuperees
$i = 2;
while ($ligne = $DB_site->fetch_array($requete)) {
    if (isset($ligne)) {
        $date = preg_match('#\d{1,2}/\d{1,2}/\d{2,4}#', $ligne['reglement_commentaires'], $m) ? $m[0] : '';

        $r = [];
        $r[] = new ooo_cell($ligne['facturid']);
        $factNumber = $ligne['numerofacture'] != 15550 ? $ligne['numerofacture'] : '';
        $r[] = new ooo_cell($factNumber);
        $r[] = new ooo_cell($ligne['numeroreglement']);
        $r[] = new ooo_cell($ligne['nom']);
        $r[] = new ooo_cell($ligne['prenom']);
        $r[] = new ooo_cell($ligne['adresse']);
        $r[] = new ooo_cell($ligne['codepostal']);
        $r[] = new ooo_cell($ligne['ville']);
        $r[] = new ooo_cell($ligne['reglement_montant']);
        $r[] = new ooo_cell($ligne['reglement_date']);
        $r[] = new ooo_cell($ligne['libelleFR']);

        /*$r[] = new ooo_cell(date("d/m/Y", $ligne['date']));
        $r[] = new ooo_cell(utf8_encode($ligne['destinataire']));
        $r[] = new ooo_cell($ligne['facturid']);
        $r[] = new ooo_cell(date("d/m/Y", $ligne['dateexpedition']));
        $r[] = new ooo_cell($ligneht, "prix", "currency");*/

        $ooo_1->addrow($i, $r);
        $i++;
    }
}

// Creation de l'entete des colonnes
$cells = [];
$cells[] = new ooo_cell('N° Commande', 'cell');
$cells[] = new ooo_cell('Fact Numero', 'cell');
$cells[] = new ooo_cell('Reglmt Numero', 'cell');
$cells[] = new ooo_cell('Nom', 'cell');
$cells[] = new ooo_cell('Prenom', 'cell');
$cells[] = new ooo_cell('Adresse', 'cell');
$cells[] = new ooo_cell('C. Postal', 'cell');
$cells[] = new ooo_cell('Ville', 'cell');
$cells[] = new ooo_cell('Montant TTC', 'cell');
$cells[] = new ooo_cell('Reglmt Date', 'cell');
$cells[] = new ooo_cell('Reglmt Type', 'cell');

$ooo_1->addrow(1, $cells);

// Ajout de l'onglet ooo_1 dans le classeur ooo
$ooo->addtable($ooo_1);

// Creation du fichier .ods contenant les donnees
$ooo->generate();