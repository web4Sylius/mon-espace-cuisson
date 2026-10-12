<?php
//Script associations de toutes les garanties produits qui n'étaient pas configuré à cause de la limite des 5000¤

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération de tous les articles du site qui :
//- sont de type "Piano", "Cuisinière", "Plaque de cuisson", "Four", "Hotte"
$aTypeArticles = array(1,2,3,4,5);

$sql = "SELECT a.artid, am.marqueid, a.typearticleid";
$sql .= " FROM article a";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " WHERE a.typearticleid IN (";
foreach($aTypeArticles as $typearticleid){
     $sql .= $typearticleid.",";
}
$sql .= "-1)";
$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){
     $aArticles[] = $row;
}

//Récupération des garanties
$sql = "SELECT *";
$sql .= " FROM garantie";
$sql .= " WHERE fournisseur = 'SFG'";
$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){
     $aGaranties[] = $row;
}

foreach($aGaranties as $la_garantie){
     
     $aMarque       = explode(',', $la_garantie['marques']);
     $aTypesArticle = explode(',', $la_garantie['types_produit']);
     
     foreach($aArticles as $l_article){
          
          if(in_array($l_article['typearticleid'], $aTypesArticle) && in_array($l_article['marqueid'], $aMarque)){
               
               $sql = "SELECT *";
               $sql .= " FROM article_garantie_liste";
               $sql .= " WHERE artid = ".$l_article['artid'];
               $sql .= " AND garantieid = ".$la_garantie['garantieid'];
               $tabGarantie = $DB_site->query_first($sql);
               
               if(empty($tabGarantie)){
                    
                    $sql = "INSERT INTO article_garantie_liste (";
                    $sql .= "artid";
                    $sql .= ", garantieid";
                    $sql .= ") VALUES (";
                    $sql .= $l_article['artid'];
                    $sql .= ", ".$la_garantie['garantieid'];
               }
          }
     }
}

function clean_prix($prix){
     $prix = str_replace('¤', '', utf8_decode($prix));
     $prix = str_replace(',', '.', $prix);
     $prix = trim($prix);
     
     return round($prix,2);
}

