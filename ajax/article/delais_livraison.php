<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid=intval($_GET['artid']);

//Sélection détail de l'article
$sql = "SELECT *";
$sql .= " FROM article";
$sql .= " WHERE artid = '" . $artid . "'";
$article = $DB_site->query_first($sql);

$d = delailivraison($article['artid']);
$jourlivraison = ajoute_jours_non_livrable($d['delailivraison']);

$heurelimite=14;

if(date("H")<$heurelimite){
	$datelimitecommande=date("d/m/Y");
} else {
	$datelimitecommande=date("d/m/Y", mktime(0,0,0,date("m"),date("d")+1));
}

//Insertion dans les articles vus si l'utilisateur n'est pas un admin
if(!isset($_COOKIE['admin_username']) && !in_array($_SERVER['REMOTE_ADDR'], $aAdminIPS)){
    $sql = "SELECT COUNT(*)";
    $sql .= " FROM article_vu";
    $sql .= " WHERE artid = ".$artid;
    $sql .= " AND s = '".$s."'";
    // echo $sql;
    $artvus = $DB_site->query_first($sql);
    if($artvus[0] == 0){
        $sql = "INSERT INTO article_vu SET";
        $sql .= " artid   = ".$artid;
        $sql .= ", nb     = 1";
        $sql .= ", s      = '".$s."'";
        $sql .= ", userid = ".intval($user_info['userid']);
        $sql .= ", date   = ".time();
        // echo $sql;
        $DB_site->query($sql);
    } else {
        $sql = "UPDATE article_vu SET";
        $sql .= " nb = nb + 1";
        $sql .= " WHERE artid = ".$artid;
        $sql .= " AND s = '".$s."'";
        // echo $sql;
        $DB_site->query($sql);
    }
}

echo $template->translate('/article/livraison_autour_du');
?> <strong><?php echo date("d/m/Y",$jourlivraison); ?></strong> <sup>(1)</sup>
<?php
echo $template->translate('/article/si_commande_avant');
echo $datelimitecommande;
echo $template->translate('/article/a');
echo $heurelimite.":00";
?>
<br>
<?php
echo $d['delailivraison'] . " " . $template->translate("/article/champs/jours_transport_inclus");
?>
