<?php
require_once("admin_global.php");
include_once("./header.php");
include_once("./menu.php");

$action  = isset($_GET['action']) ? $_GET['action'] : '';
$mois  = isset($_GET['mois']) ? $_GET['mois'] : '';
$annee  = isset($_GET['annee']) ? $_GET['annee'] : '';

?>
<h1>Exports Adcom :</h1>
<form method="get" action="export_adcom.php">
	<input type="hidden" name="action" value="link">
	<label for="start">Date début:</label>

	<input type="date" id="start" name="start"
	       value=""
	       min="2018-01-01" max="2025-12-31">

	<label for="end">Date fin:</label>       

	<input type="date" id="end" name="end"
	       value=""
	       min="2018-01-01" max="2025-12-31">

	<input type="submit" value="OK">
</form><br>

<?php

if($action=="link"){
	$ts1=$ts2=time();
	if(isset($_GET['start']) && $_GET['start'] !=''){
		$start = explode("-", $_GET['start']);
		$ts1= mktime(0,0,0,$start[1],$start[2],$start[0]);
	}
	if(isset($_GET['end']) && $_GET['end'] !=''){
		$end = explode("-", $_GET['end']);
		$ts2= mktime(0,0,0,$end[1],$end[2],$end[0]);
	}
	

	echo "Les liens du $start[2]/$start[1]/$start[0] au  $end[2]/$end[1]/$end[0] de téléchargement sont les suivants :<br><br>";
	// $nbjour=date("t", $ts1);
	$link1 = "https://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=".$ts1."&ts2=".$ts2."&mode=free&request=cde";
	$link2 = "https://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=".$ts1."&ts2=".$ts2."&mode=free&request=vente";
	$link3 = "https://dedie.ma-cave-a-vin.fr/admin/export/export_adcom.php?admin_username=9d3bb895f22bf0afa958d68c2a58ded7&ts=".$ts1."&ts2=".$ts2."&mode=free&request=article";
	echo "<p>Commandes enregistrées :<br><a href=\"".$link1."\" target=\"_blank\">".$link1."</a></p>";
	echo "<p>Meilleures ventes : <br><a href=\"".$link2."\" target=\"_blank\">".$link2."</a></p>";
	echo "<p>Articles vendus :<br><a href=\"".$link3."\" target=\"_blank\">".$link3."</a></p>";
	
}



include "footer.php";
?>