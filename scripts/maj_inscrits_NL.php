<?php
//DESINSCRIPTION D'EMAILS A LA NL

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$entete = false;
$nom_fichier = 'CSV/desabonnes_newsletter.csv';

$length	  = count_line_CSV($nom_fichier);
$tabMails = extractCSV($nom_fichier);
$count = 1;
while ($count < $length)
{
	$sql = "UPDATE utilisateur";
	$sql .= " SET allowemail = '0'";
 	$sql .= " WHERE mail = '".mysql_real_escape_string($tabMails[$count][1])."'";
     $cams = $DB_site->query($sql);
 	$count++;

}

function count_line_CSV($file){
     $row = 1;
     $i = 0;
     if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               $i++;
          }
          fclose($handle);
     }

     return $i;
}

function extractCSV($file){
     $row = 1;
     $i = 0;
     if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               $tabRetour[] = $data;
          }
          fclose($handle);
     }

     return $tabRetour;
}