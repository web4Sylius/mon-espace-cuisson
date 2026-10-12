<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

if(file_exists("418_2.csv")){
	$csv = fopen("418_2.csv", "r");
	$ligne = 1; // Ligne du .csv
	while($tab = fgetcsv($csv, 1000)){
		$count = count($tab);
		for($i = 0; $i < $count; $i++){
			if($ligne != 1){
				$req = $DB_site->query_first("SELECT * FROM utilisateur WHERE telephone LIKE '%".str_replace(";", "", substr($tab[$i],2))."%' OR portable LIKE '%".str_replace(";", "", substr($tab[$i],2))."%'");
				if($req['userid'] != ""){
					$DB_site->query("UPDATE utilisateur SET allowsms='0' WHERE userid=".$req['userid']."");
				}
			}
		}
		$ligne++;
	}
	echo "SUCCES : Tous les utilisateurs du fichier .csv ont &eacute;t&eacute; d&eacute;sabonn&eacute;s";
}else{
	echo "Fichier .CSV introuvable";
}
?>