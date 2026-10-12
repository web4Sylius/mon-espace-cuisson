<?php
/**************************************************************/
/* SCRIPT QUI GERE LES MISES A JOUR AUTOMATIQUES QUOTIDIENNES */
/* Exécution journalière, à 00h05                             */
/**************************************************************/

ini_set('display_errors',1);
$nostat   = 1;

include_once("host_config_admin.php");
require_once($rootpath."class/email.php");
require_once($rootpath."class/stock.class.php");

$DB_site = clone $DB_site2;

$today = mktime(0,0,0,date("m"),date("d"),date("Y"));

print "Prix HT ...\n";

$sql = "UPDATE article SET";
$sql .= " date_appro = 0";
$sql .= " WHERE date_appro > 0";
$sql .= " AND date_appro <= ".$today;
$DB_site->query($sql);

$sql = "UPDATE article SET";
$sql .= " prixconseilleht = prixht";
$sql .= " WHERE prixht <> prixconseilleht";
$sql .= " AND prixht - prixconseilleht BETWEEN -1 AND 1";
$DB_site->query($sql);

print "OK\n";

print "Ventes ...\n";

$table_name = uniqid('tmp_test_');
$sql = "CREATE TEMPORARY TABLE `$table_name` ("
        ."  `artid` int(10)"
        .", `ventes` int(10) unsigned NOT NULL"
        .", PRIMARY KEY (`artid`)"
        .") ENGINE = MEMORY";
$DB_site->query($sql);

$table_name_pro = uniqid('tmp_test_pro');
$sql = "CREATE TEMPORARY TABLE `$table_name_pro` ("
        ."  `artid` int(10)"
        .", `ventespro` int(10) unsigned NOT NULL"
        .", PRIMARY KEY (`artid`)"
        .") ENGINE = MEMORY";
$DB_site->query($sql);

//Mise à 0 des ventes de chaque article pour recalcul
$sql = "UPDATE article SET";
$sql .= " ventes = 0";
$DB_site->query($sql);

//Récupération des articles vendus dans les 3 derniers mois, hors lots PARTICULIER
$sql = "SELECT lf.artid, count(artid)";
$sql .= " FROM lignefacture lf";
$sql .= " INNER JOIN facture f ON (f.facturid=lf.factureid)";
$sql .= " WHERE f.valide IN (1,5,7,8)";
$sql .= " AND f.raisonsociale = ''";
$sql .= " AND artid <> 0";
$sql .= " AND f.heurefacture > ".(time() - 365 * 86400);
$sql .= " AND (";
$sql .= "     SELECT COUNT(*)";
$sql .= "     FROM lignefacturediss";
$sql .= "     WHERE factureid = f.facturid";
$sql .= "     AND artid = lf.artid";
$sql .= ") = 0";
$sql .= " GROUP BY artid";
$r = $DB_site->query($sql);

//Récupération des articles vendus dans les 3 derniers mois, hors lots PRO
$sql = "SELECT lf.artid, count(artid)";
$sql .= " FROM lignefacture lf";
$sql .= " INNER JOIN facture f ON (f.facturid=lf.factureid)";
$sql .= " WHERE f.valide IN (1,5,7,8)";
$sql .= " AND f.raisonsociale != ''";
$sql .= " AND artid <> 0";
$sql .= " AND f.heurefacture > ".(time() - 365 * 86400);
$sql .= " AND (";
$sql .= "     SELECT COUNT(*)";
$sql .= "     FROM lignefacturediss";
$sql .= "     WHERE factureid = f.facturid";
$sql .= "     AND artid = lf.artid";
$sql .= ") = 0";
$sql .= " GROUP BY artid";
$rpro = $DB_site->query($sql);

$tempsql = array();
while($count = $DB_site->fetch_array($r)){
      $tempsql[$count[0]] = intval($count[1]);
}
while($count = $DB_site->fetch_array($rpro)){
      $tempsqlpro[$count[0]] = intval($count[1]);
}

print "OK\n";

print "Ventes lots ...\n";

//Récupération des lots vendus dans les 3 derniers mois PARTICULIER
$sql = "SELECT lf.artid, count(artid)";
$sql .= " FROM lignefacturediss lf";
$sql .= " INNER JOIN facture f ON (f.facturid=lf.factureid)";
$sql .= " WHERE f.valide IN (1,5,7,8)";
$sql .= " AND f.raisonsociale = ''";
$sql .= " AND artid <> 0";
$sql .= " AND f.heurefacture > ".(time() - 365 * 86400);
$sql .= " GROUP BY artid";
$r = $DB_site->query($sql);
while($count = $DB_site->fetch_array($r)){
      $tempsql[$count[0]] += intval($count[1]);
}

//Récupération des lots vendus dans les 3 derniers mois PRO
$sql = "SELECT lf.artid, count(artid)";
$sql .= " FROM lignefacturediss lf";
$sql .= " INNER JOIN facture f ON (f.facturid=lf.factureid)";
$sql .= " WHERE f.valide IN (1,5,7,8)";
$sql .= " AND f.raisonsociale != ''";
$sql .= " AND artid <> 0";
$sql .= " AND f.heurefacture > ".(time() - 365 * 86400);
$sql .= " GROUP BY artid";
$r = $DB_site->query($sql);
while($count = $DB_site->fetch_array($r)){
      $tempsqlpro[$count[0]] += intval($count[1]);
}

//PARTICULIER
if(count($tempsql)>0){
    
    $virg = "";

    //Insertion dans la table temporaire
    $sql = "INSERT INTO ".$table_name." (";
    $sql .= "artid";
    $sql .= ",ventes";
    $sql .= ") VALUES ";
    foreach($tempsql as $artid=>$ventes){
        $sql .= $virg;
        $sql .= "(";
            $sql .= $artid;
            $sql .= ",".$ventes;
        $sql .= ")";
        
        $virg = ",";
    }
    $DB_site->query($sql);

    //Mise à jour des ventes avec les résultats insérés dans la table temporaire
    $sql = "UPDATE article, ".$table_name." SET";
    $sql .= " article.ventes = ".$table_name.".ventes";
    $sql .= " WHERE ".$table_name.".artid = article.artid";
    $DB_site->query($sql);
}
//PRO
if(count($tempsqlpro)>0){
    
    $virg = "";

    //Insertion dans la table temporaire
    $sql = "INSERT INTO ".$table_name_pro." (";
    $sql .= "artid";
    $sql .= ",ventespro";
    $sql .= ") VALUES ";
    foreach($tempsqlpro as $artid=>$ventespro){
        $sql .= $virg;
        $sql .= "(";
            $sql .= $artid;
            $sql .= ",".$ventespro;
        $sql .= ")";
        
        $virg = ",";
    }
    $DB_site->query($sql);

    //Mise à jour des ventes avec les résultats insérés dans la table temporaire
    $sql = "UPDATE article, ".$table_name_pro." SET";
    $sql .= " article.ventespro = ".$table_name_pro.".ventespro";
    $sql .= " WHERE ".$table_name_pro.".artid = article.artid";
    $DB_site->query($sql);
}
// debut modification de l'ordre d'affichage des cheques cadeaux
$k=1;
$chequescadeaux=$DB_site->query("SELECT artid FROM article WHERE catid='156' AND active='1' ORDER BY prix DESC");
while($chequecadeau=$DB_site->fetch_array($chequescadeaux)){
	$DB_site->query("UPDATE article SET ventes='".$k."' WHERE artid='".$chequecadeau[artid]."' ");
	$k++;
}
// fin modification de l'ordre d'affichage des cheques cadeaux


//Suppression de la table temporaire
$sql = "DROP TABLE `$table_name`";
$DB_site->query($sql);
//Suppression de la table temporaire
$sql = "DROP TABLE `$table_name_pro`";
$DB_site->query($sql);

print "OK\n";


print "Stock ...\n";

//Met ) jour, pour chaque article, s'il est en stock ou non
$stock = new stock($DB_site);
$stock->updatecache();

print "OK\n";

print "Actif, commandable ...\n";

//Mise à jour visibilité produits
$sql = "SELECT *";
$sql .= " FROM article_visible";
$sql .= " WHERE DATE(FROM_UNIXTIME(date)) = '".date("Y-m-d",$today)."'";
$r = $DB_site->query($sql);

while($av = $DB_site->fetch_array($r)){
    
    $sql = "UPDATE article SET";
    $sql .= " active = '".intval($av['active'])."'";
    $sql .= " WHERE artid = ".intval($av['artid']);
    $DB_site->query($sql);

    $sql = "UPDATE article_site SET";
    $sql .= " active = ".intval($av['active']);
    $sql .= " WHERE site_code = 'base'";
    $sql .= " AND artid = ".intval($av['artid']);
    $DB_site->query($sql);
}

$sql = "SELECT *";
$sql .= " FROM article_commandable";
$sql .= " WHERE date = ".$today;
$r = $DB_site->query($sql);
while($av = $DB_site->fetch_array($r)){

    $sql = "UPDATE article SET";
    $sql .= " commandable = '".intval($av['commandable'])."'";
    $sql .= " WHERE artid = ".intval($av['artid']);
    $DB_site->query($sql);
} 

print "OK\n";