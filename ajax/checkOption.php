<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid=intval($_GET['artid']);
$caractvalid=intval($_GET['caractvalid']);

$sql = "SELECT *";
$sql .= " FROM caracteristique c, caracteristiquevaleur cv, article_caractval ac";
$sql .= " WHERE c.caractid = cv.caractid";
$sql .= " AND cv.caractvalid = ac.caractvalid";
$sql .= " AND cv.caractvalid = '".$caractvalid."'";
$sql .= " AND ac.artid = '".$artid."'";
$tabCaracteristique = $DB_site->query_first($sql);

if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.ma-cave-a-vin.fr/images/caracteristiquevaleur/".$tabCaracteristique['caractvalid'].".jpg")){
     $affiche_visuel = true;
     if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/www.ma-cave-a-vin.fr/images/caracteristiquevaleur/zoom/".$tabCaracteristique['caractvalid'].".jpg")){
          $affiche_zoom = true;
     }
}

echo "<div style=\"float:left; width:100%;\">";
if($affiche_visuel){
     echo "<img src=\"/configurations/www.ma-cave-a-vin.fr/images/caracteristiquevaleur/".$tabCaracteristique['caractvalid'].".jpg\" style=\"float:left; margin: 0 5px 0 0;max-height:50px;\">";
}
echo ""
. $tabCaracteristique['libelle'.$template->langue]
. "</div>";