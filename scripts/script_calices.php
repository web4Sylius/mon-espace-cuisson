<?php
exit;
//Script pour mise à jour des températures sur les CALICES

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$entete = true;
$nom_fichier = 'calice2.csv';
$test = false;

$tabCave = extractCSV($nom_fichier);

//Boucle sur liste des garanties SFG
foreach($tabCave as $la_cave){
     
     if($la_cave[6] != ''){

          $sql = "UPDATE article SET";
          $sql .= " temperature_cave = '".utf8_encode($la_cave[6])."'";
          $sql .= ", temp_instal_min = ".$la_cave[7];
          $sql .= ", temp_instal_max = ".$la_cave[8];
          $sql .= " WHERE artcode = '".$la_cave[0]."'"; 
          $DB_site->query($sql);

     }

}

function extractCSV($file){
     $row = 1;
     if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               if($row > 1 || $entete) $tabRetour[] = $data;
               $row++;
          }
          fclose($handle);
     }
     
     return $tabRetour;
}