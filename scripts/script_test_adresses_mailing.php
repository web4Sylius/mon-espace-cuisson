<?php
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');

//Script permettant de comparer une liste d'email (CSV) avec ceux inscrits dans notre BDD
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des données du fichiers d'import
$nom_fichier = 'contacts_mailing.csv';

$tabContacts = extractCSV($nom_fichier);

echo "Emails présents dans notre BDD :<br><br>";

$i=1;
foreach($tabContacts as $le_contact){
	$le_contact[0] = utf8_encode($le_contact[0]);
	$le_contact[1] = utf8_encode($le_contact[1]);
	$le_contact[2] = utf8_encode($le_contact[2]);
	$sql = "SELECT userid, nom, prenom FROM utilisateur WHERE mail = '".$le_contact[2]."'"; 
	$aContact = $DB_site->query_first($sql);
	
	if($aContact) echo $le_contact[0].';'.$le_contact[1].';'.$le_contact[2].'<br>';
	$i++;
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