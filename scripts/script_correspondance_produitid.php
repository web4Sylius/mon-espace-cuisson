<?php
//exit;
//Script pour obtenir des références à partir d'id produits

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$entete = true;
$nom_fichier = 'script_correspondance_produitid.csv';
$test = false;

$tabProduit = extractCSV($nom_fichier);

//Boucle sur liste des garanties SFG
foreach($tabProduit as $le_produit){
		
	$sql = "SELECT artcode";
	$sql .= " FROM article";
	$sql .= " WHERE artid = ".$le_produit[0];
	$artcode = $DB_site->query_first($sql)[0];
	$aREF[$artcode] = $le_produit[0];

}

ksort($aREF);

foreach($aREF as $produit_ref => $produit_id){
	echo $produit_id.' => '.$produit_ref.'<br>';
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