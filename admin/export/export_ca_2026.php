<?php

$rootpath = '/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/';
$rootpath = '../../';
$host = 'www.ma-cave-a-vin.fr';


include_once($rootpath . 'configurations/' . $host . '/config_mysql.php');
include($rootpath . 'class/mysql.php');
include($rootpath . 'configurations/' . $host . '/config.php');
include_once($rootpath . 'class/fonctions.php');
include_once($rootpath . 'class/facture.class.php');

include_once('xlexport.php');

$DB_site2 = new DB_Sql;
$DB_site2->appname = 'boutique';
$DB_site2->appshortname = 'boutique';
$DB_site2->database = $DBname;
$DB_site2->server = $db_server;
$DB_site2->user = $DBusername;
$DB_site2->password = $DBpassword;
$DB_site2->connect();


if (!empty($_GET['date_debut']) && !empty($_GET['date_fin'])) {

    $date_debut = $_GET['date_debut'];
    $date_fin   = $_GET['date_fin'];

/*    $sql = "
        SELECT 
            f.datefacture AS date_commande,
            f.facturid,
            f.datefacture AS facture_date,
            f.dateexpedition AS dateexpedition,
            f.numerofacture AS numerofacture,
            ROUND(SUM((lf.prix * lf.qte) / (1 + lf.tva / 100)), 2) AS total_ht,
            ROUND(SUM((lf.prix * lf.qte) - (lf.prix * lf.qte / (1 + (lf.tva / 100)))), 2) AS tva,
            ROUND(SUM(lf.prix * lf.qte), 2) AS total_ttc,
            p2.libelleFR AS paysFacturation,
            p.libelleFR AS paysLivraison
        FROM facture f
        LEFT JOIN lignefacture lf ON lf.factureid = f.facturid
        LEFT JOIN pays p ON f.paysid = p.paysid
        LEFT JOIN utilisateur u ON u.userid = f.userid
        LEFT JOIN pays p2 ON u.paysid = p2.paysid
        WHERE f.valide IN (1,5,7,8)
          AND f.dateexpedition BETWEEN '$date_debut' AND '$date_fin'
        GROUP BY f.facturid
    ";*/

    $sql= "SELECT 
    f.datefacture AS date_commande,
    f.facturid,
    f.datefacture AS facture_date,
    f.dateexpedition AS dateexpedition,
    f.numerofacture AS numerofacture,
    

    ROUND(
        (SUM((lf.prix * lf.qte) / (1 + lf.tva / 100)) + (f.prixport / (1 + f.tvaport / 100))) 
        - (IFNULL(f.montantcadeau, 0) / (1 + f.tvaport / 100)), 2
    ) AS total_ht,
    

    ROUND(
        (SUM((lf.prix * lf.qte) - (lf.prix * lf.qte / (1 + (lf.tva / 100)))) + (f.prixport - (f.prixport / (1 + f.tvaport / 100)))) 
        - (IFNULL(f.montantcadeau, 0) - (IFNULL(f.montantcadeau, 0) / (1 + f.tvaport / 100))), 2
    ) AS tva,
    

    ROUND(
        SUM(lf.prix * lf.qte) + f.prixport - IFNULL(f.montantcadeau, 0), 2
    ) AS total_ttc,
    
    p2.libelleFR AS paysFacturation,
    p.libelleFR AS paysLivraison
FROM facture f
LEFT JOIN lignefacture lf ON lf.factureid = f.facturid
LEFT JOIN pays p ON f.paysid = p.paysid
LEFT JOIN utilisateur u ON u.userid = f.userid
LEFT JOIN pays p2 ON u.paysid = p2.paysid
WHERE f.valide IN (1,5,7,8)
  AND f.dateexpedition BETWEEN '$date_debut' AND '$date_fin'
GROUP BY f.facturid";


    $req = $DB_site2->query($sql);

    // === HEADERS CSV ===
    $filename = 'controle_ca_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // UTF-8 BOM pour Excel
    echo "\xEF\xBB\xBF";

    // === ENTÊTES CSV ===
    $headers = [
        'Date commande',
        'Facture ID',
        'Date facture',
        'Numéro facture',
        'Total HT',
        'TVA',
        'Total TTC',
        'Pays facturation',
        'Pays livraison'
    ];
    echo implode(';', $headers) . "\n";

    // === DONNÉES ===
    while ($row = $DB_site2->fetch_assoc($req)) {
        echo implode(';', [
                $row['date_commande'],
                $row['facturid'],
                $row['dateexpedition'],
                $row['numerofacture'],
                number_format($row['total_ht'], 2, ',', ''),
                number_format($row['tva'], 2, ',', ''),
                number_format($row['total_ttc'], 2, ',', ''),
                $row['paysFacturation'],
                $row['paysLivraison']
            ]) . "\n";
    }

    exit;
}
