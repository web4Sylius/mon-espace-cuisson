<?php
//Script Garantie
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des garanties composées de plusieurs marques
$sql = "SELECT *";
$sql.= " FROM garantie";
$sql.= " WHERE comm LIKE '%OBSELETE%'";
$query = $DB_site->query($sql);

//Résultat affecté dans un tableau
while($row = $DB_site->fetch_array($query)){
	$tabGarantie[] = $row;
}

//Boucle sur chacune des valeurs du tableau
if(is_array($tabGarantie)){
	foreach($tabGarantie as $ligne => $val){
		//DELETE des anciennes garanties (OBSELETE)
		$del = "DELETE FROM garantie";
		$del.= " WHERE garantieid = $val[garantieid]";
		$DB_site->query($del);
	}
	
	$opti = "OPTIMIZE TABLE garantie";
	$DB_site->query($del);
}

echo "Suppression des garanties \"OBSELETE\" : SUCCESS !";