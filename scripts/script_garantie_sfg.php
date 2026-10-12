<?php
//Script Garantie SFG
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Particulier - 2ans
$sqlP2 = "SELECT garantieid";
$sqlP2.= " FROM garantie";
$sqlP2.= " WHERE fournisseur = 'SFG'";
$sqlP2.= " AND garantie_client_id = 1";
$sqlP2.= " AND duree = 2";
$queryP2 = $DB_site->query($sqlP2);

while($row = $DB_site->fetch_array($queryP2)){
	$tabP2[] = $row;
}

foreach($tabP2 as $ligne => $val){
	$sql = "UPDATE garantie_tranche";
	$sql.= " SET garantie_tranche_paht = 22.5";
	$sql.= " WHERE garantieid = $val[0]";
	$sql.= " AND garantie_tranche_prix_mini = 0.00";
	$DB_site->query($sql);
}
echo "SFG pour particulier garantie 2ans : SUCCESS !<br>"; // Bravo !

//Particulier - 3ans
$sqlP3 = "SELECT garantieid";
$sqlP3.= " FROM garantie";
$sqlP3.= " WHERE fournisseur = 'SFG'";
$sqlP3.= " AND garantie_client_id = 1";
$sqlP3.= " AND duree = 3";
$queryP3 = $DB_site->query($sqlP3);

while($row = $DB_site->fetch_array($queryP3)){
	$tabP3[] = $row;
}

foreach($tabP3 as $ligne => $val){
	$sql = "UPDATE garantie_tranche";
	$sql.= " SET garantie_tranche_paht = 36";
	$sql.= " WHERE garantieid = $val[0]";
	$sql.= " AND garantie_tranche_prix_mini = 0.00";
	$DB_site->query($sql);
}
echo "SFG pour particulier garantie 3ans : SUCCESS !<br>"; // Bravo !

//Particulier - 5ans
$sqlP5 = "SELECT garantieid";
$sqlP5.= " FROM garantie";
$sqlP5.= " WHERE fournisseur = 'SFG'";
$sqlP5.= " AND garantie_client_id = 1";
$sqlP5.= " AND duree = 5";
$queryP5 = $DB_site->query($sqlP5);

while($row = $DB_site->fetch_array($queryP5)){
	$tabP5[] = $row;
}

foreach($tabP5 as $ligne => $val){
	$sql = "UPDATE garantie_tranche";
	$sql.= " SET garantie_tranche_paht = 43";
	$sql.= " WHERE garantieid = $val[0]";
	$sql.= " AND garantie_tranche_prix_mini = 0.00";
	$DB_site->query($sql);
}
echo "SFG pour particulier garantie 5ans : SUCCESS !"; // Bravo !