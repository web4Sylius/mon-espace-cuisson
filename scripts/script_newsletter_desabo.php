<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

if(file_exists("Classeur1.csv")){
	$csv = fopen("Classeur1.csv", "r");
	//$ligne = 1; // Ligne du .csv
	while($tab = fgetcsv($csv, 1000)){
		$count = count($tab);
		for($i = 0; $i < $count; $i++){
			//if($ligne != 1){
				$req = $DB_site->query("SELECT * FROM utilisateur WHERE mail='".str_replace(";", "", $tab[$i])."'");
				while ($row = $DB_site->fetch_array($req)) {
					if($row['mail'] != ""){
						$DB_site->query("UPDATE utilisateur SET allowemail='0' WHERE userid=".$row['userid']."");
					}
				}
			//}
		}
		$ligne++;
	}
	echo "SUCCES : Tous les utilisateurs du fichier .csv ont &eacute;t&eacute; d&eacute;sabonn&eacute;s";
}else{
	echo "Fichier .CSV introuvable";
}

// Décommenter $ligne et le if si le fichier .CSV la première ligne est un en-tête
?>