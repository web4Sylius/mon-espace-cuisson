<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

header('Content-Type: text/html; charset=utf-8');

//Récupération des données du fichiers d'import
$entete = true;
$nom_fichier = 'fournisseurs_conditions.csv';
$test = true;

$debut     = $entete ? 1 : 0;
$tabFournisseurs = extractCSV($nom_fichier);

foreach($tabFournisseurs as $ligne_fournisseur){
	
	$aFourn = array();

	$fournisseur_nom = utf8_encode($ligne_fournisseur[0]);
	$fournisseur_type = utf8_encode($ligne_fournisseur[1]);
	$fournisseur_code = $ligne_fournisseur[2];

	echo "<span style=\"color: black;\">\"".$fournisseur_nom."\" :</span><br>";

	$sql = "SELECT *";
	$sql .= " FROM fournisseur_condition";
	$sql .= " WHERE societe = '".mysql_real_escape_string($fournisseur_nom)."'";
	$req = $DB_site->query($sql);
	if($DB_site->num_rows($req) > 0){
		while($row = $DB_site->fetch_array($req)){
			$aFourn[] = $row;
		}

		if(count($aFourn) > 1){
			echo "<span style=\"color: red; margin-left: 30px;\">Err 02 - Comptes multiples</span><br>";
		} else {

			//Mise à jour du fournisseur
			
			$sql = "UPDATE fournisseur_condition SET";
			$sql .= " types = '".mysql_real_escape_string($fournisseur_type)."'";
			$sql .= " WHERE societe = '".mysql_real_escape_string($fournisseur_nom)."'";
			if(!$test) $DB_site->query($sql);
			echo "<span style=\"color: green; margin-left: 30px;\">... Mise à jour du type</span><br>";

			//Recherche du code 401
			if($fournisseur_type != 'service généraux'){
				
				$sql = "SELECT id";
				$sql .= " FROM fournisseur";
				$sql .= " WHERE societe = '".mysql_real_escape_string($fournisseur_nom)."'";
				$sql .= " AND type = 'facture'";
				$req = $DB_site->query($sql);

				if($DB_site->num_rows($req) > 0){

					while($row = $DB_site->fetch_array($req)){
						$sql = "UPDATE fournisseur SET";
						$sql .= " code = '".$fournisseur_code."'";
						$sql .= " WHERE id = '".$row['id']."'";
						if(!$test) $DB_site->query($sql);

						$sql = "UPDATE fournisseur_frais SET";
						$sql .= " code401 = '".$fournisseur_code."'";
						$sql .= " WHERE fournisseur = '".mysql_real_escape_string($fournisseur_nom)."'";
						if(!$test) $DB_site->query($sql);
					}
					
					echo "<span style=\"color: green; margin-left: 30px;\">... Mise à jour du code 401</span><br>";
				} else {
					echo "<span style=\"color: orange; margin-left: 30px;\">... Code 401 inexistant => Création</span><br>";

					//Récupération du pays
					$sql = "SELECT paysid";
					$sql .= " FROM fournisseur";
					$sql .= " WHERE societe = '".mysql_real_escape_string($fournisseur_nom)."'";
					$sql .= " LIMIT 1";
					$aPays = $DB_site->query_first($sql);

					//Récupération des marques
					$sql = "SELECT marques";
					$sql .= " FROM fournisseur";
					$sql .= " WHERE societe = '".mysql_real_escape_string($fournisseur_nom)."'";
					$sql .= " LIMIT 1";
					$aMarques = $DB_site->query_first($sql);

					$sql = "INSERT INTO fournisseur (";
					$sql .= "type";
					$sql .= ", societe";
					$sql .= ", paysid";
					$sql .= ", marques";
					$sql .= ", code";
					$sql .= ") VALUES (";
					$sql .= "'facture'";
					$sql .= ", '".mysql_real_escape_string($fournisseur_nom)."'";
					$sql .= ", ".$aPays['paysid'];
					$sql .= ", '".$aMarques['marques']."'";
					$sql .= ", '".$fournisseur_code."'";
					$sql .= ")";
					if(!$test) $DB_site->query($sql);
					
					echo "<span style=\"color: green; margin-left: 30px;\">... Création du contact facturation (code 401)</span><br>";

				}
			
			//Cas du fournisseur de service généraux
			} else {

				$sql = "SELECT *";
				$sql .= " FROM fournisseur_frais";
				$sql .= " WHERE fournisseur = '".mysql_real_escape_string($fournisseur_nom)."'";
				$aFourn = $DB_site->query_first($sql);

				if($aFourn){
					$sql = "UPDATE fournisseur_frais SET";
					$sql .= " code401 = '".$fournisseur_code."'";
					$sql .= " WHERE fournisseur = '".mysql_real_escape_string($fournisseur_nom)."'";
				} else {
					$sql = "INSERT INTO fournisseur_frais (";
					$sql .= "fournisseur";
					$sql .= ", code401";
					$sql .= ") VALUES (";
					$sql .= "'".mysql_real_escape_string($fournisseur_nom)."'";
					$sql .= ", '".$fournisseur_code."'";
					$sql .= ")";
				}
				if(!$test) $DB_site->query($sql);

				echo "<span style=\"color: green; margin-left: 30px;\">... Ajout du code 401</span><br>";

			}
			
		}
	} else {

		//Création du fournisseur
		echo "<span style=\"color: orange; margin-left: 30px;\">Fournisseur inexistant => Création</span><br>";

		$sql = "INSERT INTO fournisseur (";
		$sql .= "type";
		$sql .= ", societe";
		$sql .= ", paysid";
		$sql .= ", code";
		$sql .= ") VALUES (";
		$sql .= "'facture'";
		$sql .= ", '".mysql_real_escape_string($fournisseur_nom)."'";
		$sql .= ", 57";
		$sql .= ", '".$fournisseur_code."'";
		$sql .= ")";
		if(!$test) $DB_site->query($sql);

		if($fournisseur_type == 'service généraux'){

			$sql = "SELECT *";
			$sql .= " FROM fournisseur_frais";
			$sql .= " WHERE fournisseur = '".mysql_real_escape_string($fournisseur_nom)."'";
			$aFourn = $DB_site->query_first($sql);

			if($aFourn){
				$sql = "UPDATE fournisseur_frais SET";
				$sql .= " code401 = '".$fournisseur_code."'";
				$sql .= " WHERE fournisseur = '".mysql_real_escape_string($fournisseur_nom)."'";
			} else {
				$sql = "INSERT INTO fournisseur_frais (";
				$sql .= "fournisseur";
				$sql .= ", code401";
				$sql .= ") VALUES (";
				$sql .= "'".mysql_real_escape_string($fournisseur_nom)."'";
				$sql .= ", '".$fournisseur_code."'";
				$sql .= ")";
			}
			if(!$test) $DB_site->query($sql);

		}
		
		$sql = "INSERT INTO fournisseur_condition (";
		$sql .= " societe";
		$sql .= ", types";
		$sql .= ") VALUES (";
		$sql .= " '".mysql_real_escape_string($fournisseur_nom)."'";
		$sql .= ", '".mysql_real_escape_string($fournisseur_type)."'";
		$sql .= ")";
		if(!$test) $DB_site->query($sql);

		$sql = "INSERT INTO suivi_fournisseur (";
		$sql .= " fournisseur";
		$sql .= ", date";
		$sql .= ", commercial";
		$sql .= ", suivi";
		$sql .= ") VALUES (";
		$sql .= " '".mysql_real_escape_string($fournisseur_nom)."'";
		$sql .= ", '".time()."'";
		$sql .= ", 'sylvain'";
		$sql .= ", 'Création fournisseur (import CSV code 401)'";
		$sql .= ")";
		if(!$test) $DB_site->query($sql);
		
		echo "<span style=\"color: green; margin-left: 30px;\">... Création du fournisseur</span><br>";


	}
}

function extractCSV($file){
     $row = 1;
     if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               $tabRetour[] = $data;
          }
          fclose($handle);
     }
     
     return $tabRetour;
}