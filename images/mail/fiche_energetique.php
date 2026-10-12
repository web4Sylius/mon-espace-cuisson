<?php
require ("../../global.php");
if(!is_numeric($_GET['artid'])) exit;

$artid = mysqli_real_escape_string($DB_site->link_id,$_GET['artid']);

$sql = "SELECT * FROM article_fiche_energie WHERE artid=$artid";

$fiche = $DB_site->query_first($sql);

$file = $artid.".".$fiche['ext'];
$file = $rootpath."configurations/".$host."/images/energy_label/".$file;
// Type de contenu dd l'en-tête
header('Content-type: application/pdf'); 
  
header('Content-Disposition: inline; filename="' . $file . '"'); 
  
header('Content-Transfer-Encoding: binary'); 
  
header('Accept-Ranges: bytes'); 
  
// Lire le fichier
@readfile($file); 