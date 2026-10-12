<?php
header('Content-Type: text/html; charset=iso-8859-1');

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid = intval($_GET['artid']);
$caract_id = intval($_GET['caractid']);

if(!empty($artid) && !empty($caract_id)){
     $sql = "SELECT cv.caractvalid";
     $sql .= " FROM article_caractval ac";
     $sql .= " INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid)";
     $sql .= " WHERE ac.artid = ".$artid;
     $sql .= " AND cv.caractid = ".$caract_id;
     $req = $DB_site->query($sql);
     $chaine_retour = '';
     while($row = $DB_site->fetch_array($req)){
          
          if(!empty($chaine_retour)) $chaine_retour .= '|';
          $chaine_retour .= $row['caractvalid'];
     }
     echo $chaine_retour;
}