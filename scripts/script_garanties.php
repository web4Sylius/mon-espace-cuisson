<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$sql = 'SELECT artid, garantie_incluse FROM article ';
$req = $DB_site->query($sql);

while($row = $DB_site->fetch_array($req)){
	
	$garantie=str_replace("\\", "", $row[garantie_incluse]);
	
	
	$sql = "UPDATE article SET garantie_incluse='". addslashes($garantie) ."' WHERE artid=".$row[artid]." ";
	//echo $sql.'<br><br>';
	$DB_site->query($sql);

}