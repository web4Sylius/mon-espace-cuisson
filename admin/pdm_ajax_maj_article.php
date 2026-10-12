<?php
require_once("admin_global.php");

if(!isset($_GET['artid']) || empty($_GET['artid']) || !is_numeric($_GET['artid']) || !isset($_GET['pdm']) || empty($_GET['pdm'])){
     exit;
}

$tabArticle = array();
$tabInfosPDM = array();

//Récupération de la place de marché correspondante
$sql = " SELECT *";
$sql .= " FROM market_place";
$sql .= " WHERE market_place_nom2 LIKE '".$_GET['pdm']."'";
$tabPDM = $DB_site2->query_first($sql);

//Récupération de l'article correspondant
$sql = "SELECT artcode";
$sql .= " FROM article";
$sql .= " WHERE artid = ".$_GET['artid'];
$tabArticle = $DB_site2->query_first($sql);

//Récupération du statut market place
$sql = "SELECT enabled";
$sql .= " FROM market_place_champs";
$sql .= " WHERE artcode LIKE '".$tabArticle['artcode']."'";
$sql .= " AND market_place LIKE '".$_GET['pdm']."'";
$tabInfosPDM = $DB_site2->query_first($sql);

//Définition de l'action à effectuer
if(empty($tabInfosPDM)){ //Produit encore jamais activé sur PDM
     $create = true;
     $enable = true;
} else if($tabInfosPDM['enabled'] == 0){ //Produit désactivé
     $create = false;
     $enable = true;
} else { //Produit activé
     $create = false;
     $enable = false;
}

//Mise à jour du statut
if($create){
     $sql = "INSERT INTO market_place_champs (";
     $sql .= "market_place";
     $sql .= ", artcode";
     $sql .= ", enabled";
     $sql .= ", last_modified";
     $sql .= ") VALUES (";
     $sql .= "'".$_GET['pdm']."'";
     $sql .= ", '".$tabArticle['artcode']."'";
     $sql .= ", '1'";
     $sql .= ", '".mktime(0,0,0,date('n'),date('j'),date('Y'))."'";
     $sql .= ")";
} else {
     $sql = "UPDATE market_place_champs SET";
     if($enable){
          $sql .= " enabled = '1'";
     } else {
          $sql .= " enabled = '0'";
     }
     $sql .= ", last_modified = '".mktime(0,0,0,date('n'),date('j'),date('Y'))."'";
     $sql .= " WHERE artcode LIKE '".$tabArticle['artcode']."'";
     $sql .= " AND market_place LIKE '".$_GET['pdm']."'";
}
$DB_site2->query($sql);

//Changement de la couleur de l'encart
if(isset($_GET['statut']) && !empty($_GET['statut'])){
     switch($_GET['statut']){
          case 'inf':
               if($enable){
                    echo 'rouge||'.$_GET['artid'].'||'.$tabPDM['market_place_id'];
               } else {
                    echo 'orange||'.$_GET['artid'].'||'.$tabPDM['market_place_id'];
               }
               break;
          case 'sup':
               if($enable){
                    echo 'vert||'.$_GET['artid'].'||'.$tabPDM['market_place_id'];
               } else {
                    echo 'bleu||'.$_GET['artid'].'||'.$tabPDM['market_place_id'];
               }
               break;
     }
}