<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

if(file_exists("BCF9160506274.csv")){
	$csv = fopen("BCF9160506274.csv", "r");
	$ligne = 1; // Ligne du .csv
	while($tab = fgetcsv($csv, 1000, ";")){
		$count = count($tab[0]);
		for($i = 0; $i < $count; $i++){
			if($ligne != 1){
				$req = $DB_site->query_first("SELECT * FROM envoi_fournisseur WHERE facturid=".$tab[0]);
				if($req['id'] != ""){
					$DB_site->query("UPDATE envoi_fournisseur SET tarif=".$tab[1]." WHERE facturid=".$tab[0]." AND type='trspt' AND destinataire='GLS'");
				}
			}
		}
		$ligne++;
	}
	echo "SUCCES";
}else{
	echo "Fichier .CSV introuvable";
}
?>